<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Cancel/fail a NOT-yet-paid order and return its reserved stock to the shelf —
 * atomically and exactly once. Stock is reserved (decremented) at order creation,
 * so every terminal non-paid outcome (customer cancel, gateway failure, 24h expiry,
 * admin cancel, failed payment-start) must release it. A paid order's reservation is
 * a sale and is never touched here — refunds go through RestockOrder instead.
 */
class ReleaseOrderStock
{
    public function handle(Order $order, OrderStatus $status, PaymentStatus $payment, string $note): bool
    {
        return DB::transaction(function () use ($order, $status, $payment, $note) {
            /** @var Order|null $fresh */
            $fresh = Order::whereKey($order->getKey())->lockForUpdate()->first();

            // Never release a live sale, and never release twice (idempotent — a late
            // webhook + the expiry job can both target the same order).
            if (! $fresh
                || $fresh->payment_status === PaymentStatus::Paid
                || $fresh->stock_released_at !== null) {
                return false;
            }

            $from = $fresh->status->value;

            foreach ($fresh->items()->with('variant')->get() as $item) {
                $item->variant?->increment('stock_qty', (int) $item->quantity);
            }

            $fresh->update([
                'status' => $status,
                'payment_status' => $payment,
                'stock_released_at' => now(),
            ]);

            $fresh->statusHistories()->create([
                'from_status' => $from,
                'to_status' => $status->value,
                'note' => $note,
            ]);

            return true;
        });
    }
}
