<?php

namespace App\Http\Controllers;

use App\Actions\Orders\MarkOrderPaid;
use App\Models\Order;
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
        // Authenticate the webhook: when a secret is configured, the request must
        // carry it via ?secret= query or the Thawani-Webhook-Secret header.
        $webhookSecret = (string) config('services.thawani.webhook_secret');

        if ($webhookSecret !== '') {
            $provided = (string) ($request->query('secret') ?? $request->header('Thawani-Webhook-Secret'));

            abort_unless(hash_equals($webhookSecret, $provided), 403);
        }

        $ref = $request->input('data.client_reference_id', $request->input('client_reference_id'));
        $sessionId = $request->input('data.session_id', $request->input('session_id'));

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
                return response()->json(['received' => true]);
            }
            $order = $bySession;
        } else {
            $order = $bySession ?? $byRef;
        }

        if (! $order || ! $order->thawani_session_id) {
            return response()->json(['received' => true]);
        }

        try {
            $session = $this->thawani->retrieveSession($order->thawani_session_id);
        } catch (\Throwable $e) {
            // Transient failure talking to Thawani — return 500 so it retries.
            report($e);

            return response()->json(['error' => 'retrieve_failed'], 500);
        }

        if ($this->thawani->isPaid($session, (int) $order->total_baisa)) {
            $this->markOrderPaid->handle($order);
        }

        return response()->json(['received' => true]);
    }
}
