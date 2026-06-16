<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('registers a new customer and signs them in', function () {
    post('/register', [
        'name' => 'Jane Buyer',
        'email' => 'jane@example.com',
        'password' => 'Password123',
        'password_confirmation' => 'Password123',
    ])->assertRedirect(route('account.dashboard'));

    $this->assertAuthenticated();
    expect(User::where('email', 'jane@example.com')->exists())->toBeTrue();
});

it('signs in an existing customer', function () {
    $user = User::where('email', 'customer@example.com')->first();

    post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('account.dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('merges a guest cart into the user cart on login', function () {
    $variant = ProductVariant::where('stock_qty', '>', 0)->first();
    $guest = Cart::create(['token' => 'guesttok']);
    $guest->items()->create(['product_variant_id' => $variant->id, 'quantity' => 2]);

    $user = User::where('email', 'customer@example.com')->first();

    // Simulate the guest token cookie being present, then perform the merge.
    request()->cookies->set('cart_token', 'guesttok');
    app(CartService::class)->mergeGuestIntoUser($user);

    $userCart = Cart::where('user_id', $user->id)->first();

    expect($userCart->items()->where('product_variant_id', $variant->id)->value('quantity'))->toBe(2)
        ->and(Cart::find($guest->id))->toBeNull();
});

it('lets a user view their own order but forbids others', function () {
    $owner = User::factory()->create();
    $other = User::where('email', 'customer@example.com')->first();

    $order = Order::create([
        'order_number' => 'AMAL-TEST-1',
        'user_id' => $owner->id,
        'status' => OrderStatus::Pending,
        'payment_status' => PaymentStatus::Pending,
        'subtotal_baisa' => 1000,
        'total_baisa' => 1000,
        'customer_name' => 'Owner',
        'customer_email' => 'owner@example.com',
    ]);

    actingAs($other)->get('/account/orders/'.$order->order_number)->assertForbidden();
    actingAs($owner)->get('/account/orders/'.$order->order_number)->assertOk();
});
