<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        // Render in the language the customer checked out in, when captured.
        if ($order->locale) {
            $this->locale($order->locale);
        }
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Your :name order :number', [
                'name' => config('app.name'),
                'number' => $this->order->order_number,
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.order-confirmation');
    }
}
