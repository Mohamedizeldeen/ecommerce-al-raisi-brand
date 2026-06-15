<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class ThawaniService
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $payUrl,
        private readonly string $secretKey,
        private readonly string $publishableKey,
    ) {}

    public static function fromConfig(): self
    {
        $c = config('services.thawani');

        return new self(
            rtrim((string) $c['base_url'], '/'),
            rtrim((string) $c['pay_url'], '/'),
            (string) $c['secret_key'],
            (string) $c['publishable_key'],
        );
    }

    /**
     * Create a hosted checkout session and return the redirect URL for the customer.
     *
     * The order total (including shipping/discount) is sent as a single product
     * line so Thawani's computed total always equals our stored total in baisa.
     */
    public function createSession(Order $order): string
    {
        $payload = [
            'client_reference_id' => $order->order_number,
            'mode' => 'payment',
            'products' => [[
                'name' => config('app.name').' — Order '.$order->order_number,
                'quantity' => 1,
                'unit_amount' => (int) $order->total_baisa,
            ]],
            'success_url' => route('checkout.success', ['ref' => $order->order_number]),
            'cancel_url' => route('checkout.cancel', ['ref' => $order->order_number]),
            'metadata' => [
                'order_id' => $order->id,
                'customer_email' => $order->customer_email,
            ],
        ];

        $response = Http::withHeaders(['thawani-api-key' => $this->secretKey])
            ->acceptJson()
            ->post("{$this->baseUrl}/checkout/session", $payload)
            ->throw();

        $sessionId = $response->json('data.session_id');

        $order->update(['thawani_session_id' => $sessionId]);

        return "{$this->payUrl}/{$sessionId}?key={$this->publishableKey}";
    }

    /**
     * Retrieve a checkout session's current state (the authoritative payment status).
     *
     * @return array<string, mixed>
     */
    public function retrieveSession(string $sessionId): array
    {
        return Http::withHeaders(['thawani-api-key' => $this->secretKey])
            ->acceptJson()
            ->get("{$this->baseUrl}/checkout/session/{$sessionId}")
            ->throw()
            ->json('data', []);
    }

    /**
     * A session is only "paid" when Thawani says so AND the amount matches our order.
     *
     * @param  array<string, mixed>  $session
     */
    public function isPaid(array $session, int $expectedBaisa): bool
    {
        return ($session['payment_status'] ?? null) === 'paid'
            && (int) ($session['total_amount'] ?? 0) === $expectedBaisa;
    }
}
