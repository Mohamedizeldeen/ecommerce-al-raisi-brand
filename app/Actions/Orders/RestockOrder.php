<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Refund/cancel a PAID order and restore its stock. Idempotent: only a currently
 * paid order is restocked (mirrors MarkOrderPaid). Never call this on a never-paid
 * order — those (pending expiry / checkout cancel) never decremented stock.
 */
class RestockOrder
{
    public function handle(Order $order, PaymentStatus $payment = PaymentStatus::Refunded, OrderStatus $status = OrderStatus::Cancelled): bool
    {
        return DB::transaction(function () use ($order, $payment, $status) {
            /** @var Order|null $fresh */
            $fresh = Order::whereKey($order->getKey())->lockForUpdate()->first();

            if (! $fresh || $fresh->payment_status !== PaymentStatus::Paid) {
                return false;
            }

            $from = $fresh->status->value;

            foreach ($fresh->items()->with('variant')->get() as $item) {
                $item->variant?->increment('stock_qty', (int) $item->quantity);
            }

            $fresh->update(['payment_status' => $payment, 'status' => $status]);

            $fresh->statusHistories()->create([
                'from_status' => $from,
                'to_status' => $status->value,
                'note' => $payment === PaymentStatus::Refunded
                    ? 'Refunded by admin — stock restored.'
                    : 'Cancelled by admin — stock restored.',
            ]);

            return true;
        });
    }
}
