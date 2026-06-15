<?php

use App\Enums\PaymentStatus;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->user = User::where('email', 'customer@example.com')->first();

    config([
        'services.thawani.base_url' => 'https://uatcheckout.thawani.om/api/v1',
        'services.thawani.pay_url' => 'https://uatcheckout.thawani.om/pay',
        'services.thawani.secret_key' => 'sk_test',
        'services.thawani.publishable_key' => 'pk_test',
        'services.thawani.mode' => 'uat',
    ]);
});

/** Fake the Thawani API: create returns a session id; retrieve reports the order paid. */
function fakeThawaniPaid(): void
{
    Http::fake([
        '*/checkout/session/*' => function ($request) {
            $sessionId = basename(parse_url($request->url(), PHP_URL_PATH));
            $order = Order::where('thawani_session_id', $sessionId)->first();

            return Http::response(['data' => [
                'payment_status' => 'paid',
                'total_amount' => (int) $order->total_baisa,
            ]], 200);
        },
        '*/checkout/session' => Http::response(['data' => ['session_id' => 'sess_TEST']], 200),
    ]);
}

function placeOrder(User $user, ProductVariant $variant, int $qty): Order
{
    actingAs($user)->post('/cart/add', ['variant_id' => $variant->id, 'quantity' => $qty]);
    actingAs($user)->post('/checkout', [
        'customer_name' => 'Test Buyer',
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '+96890000000',
        'shipping_address_line1' => '123 Heritage Way',
        'shipping_city' => 'Muscat',
    ]);

    return Order::latest('id')->first();
}

it('creates an order and redirects to the Thawani hosted page', function () {
    fakeThawaniPaid();
    $variant = ProductVariant::where('stock_qty', '>', 0)->first();

    actingAs($this->user)->post('/cart/add', ['variant_id' => $variant->id, 'quantity' => 1]);
    $response = actingAs($this->user)->post('/checkout', [
        'customer_name' => 'Test Buyer',
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '+96890000000',
        'shipping_address_line1' => '123 Heritage Way',
        'shipping_city' => 'Muscat',
    ]);

    $order = Order::first();

    $response->assertStatus(302);
    expect($response->headers->get('Location'))->toContain('sess_TEST')->toContain('key=pk_test');
    expect($order->thawani_session_id)->toBe('sess_TEST');
    expect($order->payment_status)->toBe(PaymentStatus::Pending);
});

it('marks the order paid and decrements stock on the success return', function () {
    fakeThawaniPaid();
    $variant = ProductVariant::where('stock_qty', '>', 2)->first();
    $startStock = $variant->stock_qty;

    $order = placeOrder($this->user, $variant, 2);

    actingAs($this->user)
        ->get('/checkout/success?ref='.$order->order_number)
        ->assertRedirect(route('orders.show', $order));

    $order->refresh();
    expect($order->payment_status)->toBe(PaymentStatus::Paid)
        ->and($order->paid_at)->not->toBeNull()
        ->and($variant->fresh()->stock_qty)->toBe($startStock - 2);
});

it('does not double-decrement stock when the webhook and success both fire', function () {
    fakeThawaniPaid();
    $variant = ProductVariant::where('stock_qty', '>', 2)->first();
    $startStock = $variant->stock_qty;

    $order = placeOrder($this->user, $variant, 2);

    // Webhook lands first (body is only a trigger; status is re-verified server-side).
    post('/thawani/webhook', ['data' => ['client_reference_id' => $order->order_number]])->assertOk();
    // Then the browser success redirect lands.
    actingAs($this->user)->get('/checkout/success?ref='.$order->order_number);

    expect($variant->fresh()->stock_qty)->toBe($startStock - 2)
        ->and(Order::find($order->id)->payment_status)->toBe(PaymentStatus::Paid)
        ->and(Order::find($order->id)->statusHistories()->where('to_status', 'paid')->count())->toBe(1);
});

it('sends an order confirmation email once the order is paid', function () {
    Mail::fake();
    fakeThawaniPaid();
    $variant = ProductVariant::where('stock_qty', '>', 0)->first();
    $order = placeOrder($this->user, $variant, 1);

    actingAs($this->user)->get('/checkout/success?ref='.$order->order_number);

    Mail::assertSent(OrderConfirmationMail::class, fn ($mail) => $mail->order->id === $order->id);
});
