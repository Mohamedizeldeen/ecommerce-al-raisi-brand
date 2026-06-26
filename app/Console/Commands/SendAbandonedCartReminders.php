<?php

namespace App\Console\Commands;

use App\Mail\AbandonedCartMail;
use App\Models\Cart;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAbandonedCartReminders extends Command
{
    protected $signature = 'cart:send-abandoned';

    protected $description = 'Email signed-in customers a reminder about carts abandoned within the window.';

    private const REMIND_AFTER_HOURS = 4;

    private const GIVE_UP_AFTER_HOURS = 72;

    public function handle(): int
    {
        // Only carts that belong to a known customer (we have an email), with items,
        // gone quiet between 4h and 72h ago, and not yet reminded.
        $carts = Cart::query()
            ->whereNotNull('user_id')
            ->whereNull('reminder_sent_at')
            ->where('updated_at', '<', now()->subHours(self::REMIND_AFTER_HOURS))
            ->where('updated_at', '>', now()->subHours(self::GIVE_UP_AFTER_HOURS))
            ->whereHas('items')
            ->with('user')
            ->get();

        $sent = 0;

        foreach ($carts as $cart) {
            if (! $cart->user?->email) {
                continue;
            }

            Mail::to($cart->user->email)->queue(new AbandonedCartMail($cart));

            // Stamp without bumping updated_at (query-builder update skips timestamps).
            Cart::whereKey($cart->getKey())->update(['reminder_sent_at' => now()]);
            $sent++;
        }

        $this->info("Sent {$sent} abandoned-cart reminder(s).");

        return self::SUCCESS;
    }
}
