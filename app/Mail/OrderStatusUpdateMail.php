<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * A transactional update for a post-payment status change (shipped / cancelled /
 * refunded). $headline and $body are translation keys rendered through __() in the
 * view, so the message localises to the order's checkout locale (set below).
 */
class OrderStatusUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $headline,
        public string $body,
        public bool $showTracking = false,
    ) {
        // Render in the language the customer shopped in, when we captured it.
        if ($order->locale) {
            $this->locale($order->locale);
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __($this->headline).' — '.config('app.name').' '.$this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.order-status');
    }
}
