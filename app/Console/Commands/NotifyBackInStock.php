<?php

namespace App\Console\Commands;

use App\Mail\BackInStockMail;
use App\Models\StockNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyBackInStock extends Command
{
    protected $signature = 'stock:notify-restocked';

    protected $description = 'Email customers when a variant they asked about is back in stock.';

    public function handle(): int
    {
        $pending = StockNotification::query()
            ->whereNull('notified_at')
            ->whereHas('variant', fn ($q) => $q->where('is_active', true)->where('stock_qty', '>', 0))
            ->with('variant.product')
            ->get();

        $sent = 0;

        foreach ($pending as $notification) {
            if (! $notification->variant) {
                continue;
            }

            Mail::to($notification->email)->queue(new BackInStockMail($notification->variant));
            $notification->update(['notified_at' => now()]);
            $sent++;
        }

        $this->info("Sent {$sent} back-in-stock notification(s).");

        return self::SUCCESS;
    }
}
