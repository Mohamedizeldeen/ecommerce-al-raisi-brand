<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Console\Command;

class ExpireStalePendingOrders extends Command
{
    protected $signature = 'orders:expire-stale';

    protected $description = 'Cancel orders still pending/unpaid and older than 24 hours.';

    public function handle(): int
    {
        $cutoff = now()->subHours(24);

        $orders = Order::query()
            ->where('status', OrderStatus::Pending)
            ->where('payment_status', '!=', PaymentStatus::Paid)
            ->where('created_at', '<', $cutoff)
            ->get();

        foreach ($orders as $order) {
            $from = $order->payment_status->value;

            $order->update([
                'status' => OrderStatus::Cancelled,
                'payment_status' => PaymentStatus::Cancelled,
            ]);

            $order->statusHistories()->create([
                'from_status' => $from,
                'to_status' => OrderStatus::Cancelled->value,
                'note' => 'Order auto-cancelled after 24h without payment.',
            ]);
        }

        $this->info("Expired {$orders->count()} stale pending order(s).");

        return self::SUCCESS;
    }
}
