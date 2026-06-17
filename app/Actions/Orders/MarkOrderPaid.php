<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderPaid;
use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class MarkOrderPaid
{
    /**
     * Idempotently transition an order to paid: decrement variant stock, write a
     * status-history row and fire the OrderPaid event. Safe to call multiple times
     * (e.g. webhook + success redirect racing) — only the first call does the work.
     */
    public function handle(Order $order): bool
    {
        $transitioned = DB::transaction(function () use ($order) {
            /** @var Order|null $fresh */
            $fresh = Order::whereKey($order->getKey())->lockForUpdate()->first();

            if (! $fresh || $fresh->payment_status === PaymentStatus::Paid) {
                return false;
            }

            $from = $fresh->payment_status->value;

            $fresh->update([
                'payment_status' => PaymentStatus::Paid,
                'status' => OrderStatus::Processing,
                'paid_at' => now(),
            ]);

            foreach ($fresh->items()->with('variant')->get() as $item) {
                $variant = $item->variant;

                if ($variant) {
                    $decrement = min($item->quantity, $variant->stock_qty);

                    if ($decrement > 0) {
                        $variant->decrement('stock_qty', $decrement);
                    }
                }
            }

            // Consume the coupon once, only while still within its usage limit —
            // the conditional WHERE makes this atomic against concurrent redemptions.
            if ($fresh->coupon_code) {
                Coupon::where('code', $fresh->coupon_code)
                    ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
                    ->increment('used_count');
            }

            $fresh->statusHistories()->create([
                'from_status' => $from,
                'to_status' => PaymentStatus::Paid->value,
                'note' => 'Payment confirmed via Thawani.',
            ]);

            return true;
        });

        if ($transitioned) {
            OrderPaid::dispatch($order->refresh());
        }

        return $transitioned;
    }
}
