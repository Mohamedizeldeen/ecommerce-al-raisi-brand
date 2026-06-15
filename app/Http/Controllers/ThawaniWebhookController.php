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
        $ref = $request->input('data.client_reference_id', $request->input('client_reference_id'));
        $sessionId = $request->input('data.session_id', $request->input('session_id'));

        $order = Order::query()
            ->when($ref, fn ($q) => $q->orWhere('order_number', $ref))
            ->when($sessionId, fn ($q) => $q->orWhere('thawani_session_id', $sessionId))
            ->first();

        if (! $order || ! $order->thawani_session_id) {
            return response()->json(['received' => true]);
        }

        try {
            $session = $this->thawani->retrieveSession($order->thawani_session_id);

            if ($this->thawani->isPaid($session, (int) $order->total_baisa)) {
                $this->markOrderPaid->handle($order);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json(['received' => true]);
    }
}
