<?php

namespace App\Http\Controllers;

use App\Actions\Orders\MarkOrderPaid;
use App\Actions\Orders\ReleaseOrderStock;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\PaymentEvent;
use App\Services\Payments\ThawaniService;
use Illuminate\Http\Request;

class ThawaniWebhookController extends Controller
{
    public function __construct(
        private readonly ThawaniService $thawani,
        private readonly MarkOrderPaid $markOrderPaid,
    ) {}

    /**
     * Thawani posts payment events here. The body is treated only as a trigger:
     * we re-fetch the session ourselves and confirm the authoritative status.
     */
    public function __invoke(Request $request)
    {
        // Authenticate the webhook when a secret is configured. Prefer the
        // Thawani-Webhook-Secret header (kept out of web-server access logs); fall
        // back to the ?secret= query param only for Thawani setups that can't send a
        // custom header. The body is re-verified against Thawani regardless.
        $webhookSecret = (string) config('services.thawani.webhook_secret');

        if ($webhookSecret === '') {
            // Fail closed in production: a missing secret would otherwise accept any
            // anonymous POST (DB pollution, griefing). Locally we allow an empty
            // secret so the webhook stays easy to exercise in tests/dev.
            abort_if(app()->isProduction(), 403);
        } else {
            $provided = (string) ($request->header('Thawani-Webhook-Secret') ?: $request->query('secret', ''));

            abort_unless(hash_equals($webhookSecret, $provided), 403);
        }

        $ref = $request->input('data.client_reference_id', $request->input('client_reference_id'));
        $sessionId = $request->input('data.session_id', $request->input('session_id'));

        // Record every authenticated webhook hit. The outcome is refined as the
        // deterministic flow below resolves it.
        $event = PaymentEvent::create([
            'provider' => 'thawani',
            'thawani_session_id' => $sessionId,
            'reference' => $ref,
            'payload' => $request->all(),
        ]);

        // Resolve deterministically: prefer the unique session id; only fall back to
        // order_number when no session id is supplied. If both are present they must
        // point at the same order, otherwise we ignore the event.
        $bySession = $sessionId
            ? Order::where('thawani_session_id', $sessionId)->first()
            : null;
        $byRef = $ref
            ? Order::where('order_number', $ref)->first()
            : null;

        if ($sessionId && $ref) {
            if (! $bySession || ! $byRef || $bySession->getKey() !== $byRef->getKey()) {
                $event->update(['outcome' => 'unmatched']);

                return response()->json(['received' => true]);
            }
            $order = $bySession;
        } else {
            $order = $bySession ?? $byRef;
        }

        if (! $order || ! $order->thawani_session_id) {
            $event->update(['outcome' => 'unmatched']);

            return response()->json(['received' => true]);
        }

        $event->update([
            'order_id' => $order->getKey(),
            'amount_baisa' => (int) $order->total_baisa,
        ]);

        try {
            $session = $this->thawani->retrieveSession($order->thawani_session_id);
        } catch (\Throwable $e) {
            // Transient failure talking to Thawani — return 500 so it retries.
            $event->update(['outcome' => 'error']);

            report($e);

            return response()->json(['error' => 'retrieve_failed'], 500);
        }

        if ($this->thawani->isPaid($session, (int) $order->total_baisa)) {
            $this->markOrderPaid->handle($order);
            $event->update(['outcome' => 'paid']);

            return response()->json(['received' => true]);
        }

        $event->update(['outcome' => 'not_paid']);

        // Conservatively fail the order only on a clearly terminal, non-paid session
        // state. "isPaid" already rejects amount mismatches, so here we look strictly
        // at Thawani's payment_status. We never fail on states that may still settle.
        $paymentStatus = (string) ($session['payment_status'] ?? '');

        if (
            in_array($paymentStatus, ['cancelled', 'canceled', 'expired'], true)
            && $order->payment_status !== PaymentStatus::Paid
            && $order->payment_status !== PaymentStatus::Failed
        ) {
            // Terminal failure: release the reservation and move the order to a terminal
            // state so the expiry job won't re-process it.
            app(ReleaseOrderStock::class)->handle(
                $order, OrderStatus::Cancelled, PaymentStatus::Failed,
                'Payment failed (Thawani session '.$paymentStatus.') — reservation released.'
            );
        }

        return response()->json(['received' => true]);
    }
}
