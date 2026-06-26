<?php

use App\Mail\AbandonedCartMail;
use App\Models\Cart;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->user = User::where('email', 'customer@example.com')->first();
    $this->variant = ProductVariant::where('stock_qty', '>', 0)->first();
});

function abandonedUserCart(int $userId, ProductVariant $variant, int $ageHours): Cart
{
    $cart = Cart::create(['user_id' => $userId]);
    $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 1]);
    Cart::whereKey($cart->id)->update(['updated_at' => now()->subHours($ageHours)]);

    return $cart->fresh();
}

it('emails a reminder for a cart abandoned within the window', function () {
    Mail::fake();
    $cart = abandonedUserCart($this->user->id, $this->variant, 5);

    artisan('cart:send-abandoned')->assertExitCode(0);

    Mail::assertQueued(AbandonedCartMail::class);
    expect($cart->fresh()->reminder_sent_at)->not->toBeNull();
});

it('does not remind a freshly active cart', function () {
    Mail::fake();
    abandonedUserCart($this->user->id, $this->variant, 1); // too recent

    artisan('cart:send-abandoned');

    Mail::assertNotQueued(AbandonedCartMail::class);
});

it('does not remind the same cart twice', function () {
    Mail::fake();
    $cart = abandonedUserCart($this->user->id, $this->variant, 5);
    Cart::whereKey($cart->id)->update(['reminder_sent_at' => now()->subHour()]);

    artisan('cart:send-abandoned');

    Mail::assertNotQueued(AbandonedCartMail::class);
});

it('ignores guest carts (no email on file)', function () {
    Mail::fake();
    $cart = Cart::create(['token' => 'guesttok']);
    $cart->items()->create(['product_variant_id' => $this->variant->id, 'quantity' => 1]);
    Cart::whereKey($cart->id)->update(['updated_at' => now()->subHours(5)]);

    artisan('cart:send-abandoned');

    Mail::assertNotQueued(AbandonedCartMail::class);
});
