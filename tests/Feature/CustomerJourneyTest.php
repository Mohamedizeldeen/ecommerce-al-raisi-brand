<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function makeGuestOrder(string $number, string $email): Order
{
    return Order::create([
        'order_number' => $number,
        'status' => OrderStatus::Processing,
        'payment_status' => PaymentStatus::Paid,
        'subtotal_baisa' => 1000,
        'total_baisa' => 1000,
        'currency' => 'OMR',
        'customer_name' => 'Guest Buyer',
        'customer_email' => $email,
        'shipping_country' => 'Oman',
    ]);
}

it('lets a guest look up their order with the matching email (case-insensitive)', function () {
    $order = makeGuestOrder('AMAL-LOOKUP-1', 'guest@example.com');

    post('/order-lookup', ['order_number' => $order->order_number, 'email' => 'GUEST@example.com'])
        ->assertRedirect(route('orders.show', $order));

    // The lookup granted session access, so the order page is now viewable.
    get(route('orders.show', $order))->assertOk()->assertSee($order->order_number);
});

it('rejects an order lookup with the wrong email and grants no access', function () {
    $order = makeGuestOrder('AMAL-LOOKUP-2', 'guest@example.com');

    post('/order-lookup', ['order_number' => $order->order_number, 'email' => 'wrong@example.com'])
        ->assertSessionHasErrors('order_number');

    get(route('orders.show', $order))->assertForbidden();
});

it('lets a customer reorder available items from a past order', function () {
    $user = User::where('email', 'customer@example.com')->first();
    $variant = ProductVariant::where('stock_qty', '>', 0)->first();

    $order = Order::create([
        'order_number' => 'AMAL-REORDER-1',
        'user_id' => $user->id,
        'status' => OrderStatus::Completed,
        'payment_status' => PaymentStatus::Paid,
        'subtotal_baisa' => $variant->price_baisa,
        'total_baisa' => $variant->price_baisa,
        'currency' => 'OMR',
        'customer_name' => 'Cust',
        'customer_email' => $user->email,
    ]);
    $order->items()->create([
        'product_variant_id' => $variant->id,
        'product_id' => $variant->product_id,
        'name' => 'Test piece',
        'variant_label' => $variant->label,
        'sku' => $variant->sku,
        'price_baisa' => $variant->price_baisa,
        'quantity' => 1,
        'line_total_baisa' => $variant->price_baisa,
    ]);

    actingAs($user)->post(route('account.orders.reorder', $order))
        ->assertRedirect(route('cart.index'));

    $cart = Cart::where('user_id', $user->id)->first();
    expect($cart->items()->where('product_variant_id', $variant->id)->exists())->toBeTrue();
});

it('renders the forgot-password page and emails a reset link', function () {
    Notification::fake();

    get('/forgot-password')->assertOk();

    $user = User::where('email', 'customer@example.com')->first();
    post('/forgot-password', ['email' => $user->email])->assertSessionHas('success');

    Notification::assertSentTo($user, ResetPassword::class);
});

it('resets the password with a valid token', function () {
    $user = User::where('email', 'customer@example.com')->first();
    $token = Password::createToken($user);

    post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NewPass123',
        'password_confirmation' => 'NewPass123',
    ])->assertRedirect(route('login'));

    expect(Hash::check('NewPass123', $user->fresh()->password))->toBeTrue();
});
