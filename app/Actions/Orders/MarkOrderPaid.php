<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderPaid;
use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

            $shortfalls = [];

            foreach ($fresh->items()->with('variant')->get() as $item) {
                $variant = $item->variant;

                if (! $variant) {
                    continue;
                }

                $available = (int) $variant->stock_qty;
                $decrement = min((int) $item->quantity, $available);

                if ($decrement > 0) {
                    $variant->decrement('stock_qty', $decrement);
                }

                // Stock fell below the ordered quantity between checkout and payment
                // (a concurrent order or an admin edit). We still charge the full order,
                // so flag the shortfall for staff to reconcile instead of silently
                // absorbing it (min() above already prevents negative stock).
                if ((int) $item->quantity > $available) {
                    $shortfalls[] = trim($item->name.' '.$item->variant_label)
                        .' (ordered '.(int) $item->quantity.', had '.$available.')';
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

            if ($shortfalls !== []) {
                $fresh->statusHistories()->create([
                    'from_status' => PaymentStatus::Paid->value,
                    'to_status' => PaymentStatus::Paid->value,
                    'note' => 'Stock shortfall at payment — fulfil manually: '.implode('; ', $shortfalls),
                ]);

                Log::warning('Order oversold at payment confirmation', [
                    'order_number' => $fresh->order_number,
                    'shortfalls' => $shortfalls,
                ]);
            }

            return true;
        });

        if ($transitioned) {
            OrderPaid::dispatch($order->refresh());
        }

        return $transitioned;
    }
}
