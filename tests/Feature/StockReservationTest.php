<?php

use App\Actions\Orders\ReleaseOrderStock;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->user = User::where('email', 'customer@example.com')->first();
    // Fake the Thawani session-create call so checkout can complete without network.
    Http::fake(['*/checkout/session' => Http::response(['data' => ['session_id' => 'sess_TEST']], 200)]);
    config([
        'services.thawani.base_url' => 'https://uatcheckout.thawani.om/api/v1',
        'services.thawani.pay_url' => 'https://uatcheckout.thawani.om/pay',
        'services.thawani.secret_key' => 'sk_test',
        'services.thawani.publishable_key' => 'pk_test',
        'services.thawani.mode' => 'uat',
    ]);
});

function placePendingOrder(User $user, ProductVariant $variant, int $qty = 1): Order
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

it('reserves stock at order creation, before payment', function () {
    $variant = ProductVariant::where('stock_qty', '>', 2)->first();
    $start = (int) $variant->stock_qty;

    $order = placePendingOrder($this->user, $variant, 2);

    expect($order->payment_status)->toBe(PaymentStatus::Pending)
        ->and((int) $variant->fresh()->stock_qty)->toBe($start - 2);
});

it('releases reserved stock when the customer cancels at checkout', function () {
    $variant = ProductVariant::where('stock_qty', '>', 2)->first();
    $start = (int) $variant->stock_qty;

    $order = placePendingOrder($this->user, $variant, 2);
    expect((int) $variant->fresh()->stock_qty)->toBe($start - 2);

    actingAs($this->user)->get('/checkout/cancel');

    expect((int) $variant->fresh()->stock_qty)->toBe($start)
        ->and($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

it('releases reserved stock when a stale pending order expires', function () {
    $variant = ProductVariant::where('stock_qty', '>', 2)->first();
    $start = (int) $variant->stock_qty;

    $order = placePendingOrder($this->user, $variant, 2);
    $order->update(['created_at' => now()->subHours(25)]);

    Artisan::call('orders:expire-stale');

    expect((int) $variant->fresh()->stock_qty)->toBe($start)
        ->and($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

it('does not double-release reserved stock (idempotent)', function () {
    $variant = ProductVariant::where('stock_qty', '>', 2)->first();
    $start = (int) $variant->stock_qty;

    $order = placePendingOrder($this->user, $variant, 2);
    $action = app(ReleaseOrderStock::class);

    expect($action->handle($order, OrderStatus::Cancelled, PaymentStatus::Cancelled, 'first'))->toBeTrue();
    expect((int) $variant->fresh()->stock_qty)->toBe($start);

    expect($action->handle($order->fresh(), OrderStatus::Cancelled, PaymentStatus::Cancelled, 'second'))->toBeFalse();
    expect((int) $variant->fresh()->stock_qty)->toBe($start); // not restored twice
});

it('prevents overselling: the last unit cannot be reserved twice', function () {
    $variant = ProductVariant::first();
    $variant->update(['is_active' => true, 'stock_qty' => 1]);

    // First buyer reserves the single unit at checkout.
    placePendingOrder($this->user, $variant, 1);
    expect((int) $variant->fresh()->stock_qty)->toBe(0);

    // A second buyer can no longer even add it to the bag.
    $second = User::factory()->create();
    actingAs($second)
        ->post('/cart/add', ['variant_id' => $variant->id])
        ->assertSessionHas('error');
});
