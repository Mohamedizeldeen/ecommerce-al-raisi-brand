<?php

use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->user = User::where('email', 'customer@example.com')->first();
});

it('adds to the cart, checks out, and creates an order with price snapshots', function () {
    Http::fake(['*/checkout/session' => Http::response(['data' => ['session_id' => 'sess_TEST']], 200)]);

    $variant = ProductVariant::where('stock_qty', '>', 1)->with('product')->first();

    actingAs($this->user)
        ->post('/cart/add', ['variant_id' => $variant->id, 'quantity' => 2])
        ->assertRedirect(route('cart.index'));

    actingAs($this->user)->get('/cart')->assertOk()->assertSee($variant->product->name);

    $response = actingAs($this->user)->post('/checkout', [
        'customer_name' => 'Test Buyer',
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '+96890000000',
        'shipping_address_line1' => '123 Heritage Way',
        'shipping_city' => 'Muscat',
    ]);

    $order = Order::first();

    expect($order)->not->toBeNull()
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->quantity)->toBe(2)
        ->and($order->items->first()->price_baisa)->toBe($variant->price_baisa)
        ->and($order->total_baisa)->toBe($variant->price_baisa * 2 + $order->shipping_baisa)
        ->and($order->thawani_session_id)->toBe('sess_TEST');

    // Checkout now redirects the customer to the Thawani hosted payment page.
    $response->assertStatus(302);
});

it('applies the WELCOME10 coupon for a 10% discount', function () {
    $variant = ProductVariant::where('stock_qty', '>', 0)->first();

    actingAs($this->user)->post('/cart/add', ['variant_id' => $variant->id, 'quantity' => 1]);
    actingAs($this->user)->post('/cart/coupon', ['code' => 'WELCOME10'])->assertSessionHas('success');

    actingAs($this->user)->get('/cart')
        ->assertOk()
        ->assertSee('Discount');
});

it('blocks adding an out-of-stock variant', function () {
    $variant = ProductVariant::first();
    $variant->update(['stock_qty' => 0]);

    actingAs($this->user)
        ->post('/cart/add', ['variant_id' => $variant->id])
        ->assertSessionHas('error');
});

it('does not deadlock checkout when a bagged item sells out — it removes it and asks to review', function () {
    $variant = ProductVariant::first();
    $variant->update(['is_active' => true, 'stock_qty' => 5]);

    actingAs($this->user)->post('/cart/add', ['variant_id' => $variant->id, 'quantity' => 2]);

    // It sells out after it's in the bag (concurrent purchase / admin edit).
    $variant->update(['stock_qty' => 0]);

    // Checkout no longer throws and bounces forever: it reconciles, redirects to the
    // bag with a notice, and creates no order.
    actingAs($this->user)->post('/checkout', [
        'customer_name' => 'Test Buyer',
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '+96890000000',
        'shipping_address_line1' => '123 Heritage Way',
        'shipping_city' => 'Muscat',
    ])->assertRedirect(route('cart.index'))->assertSessionHas('error');

    expect(Order::count())->toBe(0)
        ->and(Cart::where('user_id', $this->user->id)->first()->items()->count())->toBe(0);
});

it('clamps an over-stock quantity at checkout and asks the customer to review', function () {
    $variant = ProductVariant::first();
    $variant->update(['is_active' => true, 'stock_qty' => 5]);

    actingAs($this->user)->post('/cart/add', ['variant_id' => $variant->id, 'quantity' => 4]);

    // Stock drops below the bagged quantity.
    $variant->update(['stock_qty' => 1]);

    actingAs($this->user)->post('/checkout', [
        'customer_name' => 'Test Buyer',
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '+96890000000',
        'shipping_address_line1' => '123 Heritage Way',
        'shipping_city' => 'Muscat',
    ])->assertRedirect(route('cart.index'))->assertSessionHas('error');

    expect(Order::count())->toBe(0)
        ->and(Cart::where('user_id', $this->user->id)->first()->items()->first()->quantity)->toBe(1);
});

it('self-heals the bag when viewed after an item sells out', function () {
    $variant = ProductVariant::first();
    $variant->update(['is_active' => true, 'stock_qty' => 5]);

    actingAs($this->user)->post('/cart/add', ['variant_id' => $variant->id, 'quantity' => 2]);
    $variant->update(['stock_qty' => 0]);

    actingAs($this->user)->get('/cart')
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas('error');

    expect(Cart::where('user_id', $this->user->id)->first()->items()->count())->toBe(0);
});
