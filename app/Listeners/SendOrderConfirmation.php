<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Mail\OrderConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendOrderConfirmation implements ShouldQueue
{
    /** Retry a few times to ride out transient SMTP hiccups (greylisting, timeouts). */
    public int $tries = 3;

    /** @var array<int, int> Seconds to wait between successive retries. */
    public array $backoff = [60, 300, 900];

    public function handle(OrderPaid $event): void
    {
        Mail::to($event->order->customer_email)
            ->send(new OrderConfirmationMail($event->order));
    }

    /**
     * After all retries are exhausted, make the failure visible instead of letting
     * a paying customer's receipt vanish silently: log it and append a status-history
     * note so staff can follow up manually.
     */
    public function failed(OrderPaid $event, Throwable $e): void
    {
        Log::error('Order confirmation email failed to send', [
            'order_number' => $event->order->order_number,
            'error' => $e->getMessage(),
        ]);

        $event->order->statusHistories()->create([
            'from_status' => $event->order->status->value,
            'to_status' => $event->order->status->value,
            'note' => 'Confirmation email failed to send after retries — please follow up with the customer.',
        ]);
    }
}
