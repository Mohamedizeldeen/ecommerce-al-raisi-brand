<?php

namespace App\Console\Commands;

use App\Actions\Orders\ReleaseOrderStock;
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
            // ReleaseOrderStock locks the row and re-checks it isn't paid, so a payment
            // that settled just after the cutoff is never wrongly cancelled, and the
            // reserved stock is returned to the shelf.
            app(ReleaseOrderStock::class)->handle(
                $order, OrderStatus::Cancelled, PaymentStatus::Cancelled,
                'Order auto-cancelled after 24h without payment — reservation released.'
            );
        }

        $this->info("Expired {$orders->count()} stale pending order(s).");

        return self::SUCCESS;
    }
}
