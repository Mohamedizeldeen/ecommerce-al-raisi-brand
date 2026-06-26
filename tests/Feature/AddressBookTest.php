<?php

use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('adds an address and makes the first one default', function () {
    $user = User::where('email', 'customer@example.com')->first();

    actingAs($user)->post(route('account.addresses.store'), [
        'name' => 'Home',
        'line1' => '1 Heritage Way',
        'city' => 'Muscat',
    ])->assertRedirect();

    $address = $user->addresses()->first();
    expect($address)->not->toBeNull()
        ->and($address->is_default)->toBeTrue();
});

it('forbids managing another customer address', function () {
    $owner = User::factory()->create();
    $address = $owner->addresses()->create(['name' => 'X', 'line1' => '1', 'city' => 'M']);
    $other = User::where('email', 'customer@example.com')->first();

    actingAs($other)->delete(route('account.addresses.destroy', $address))->assertForbidden();
});

it('prefills checkout from the default address', function () {
    $user = User::where('email', 'customer@example.com')->first();
    $variant = ProductVariant::where('stock_qty', '>', 0)->first();
    $user->addresses()->create(['name' => 'Home', 'line1' => '1 Heritage Way', 'city' => 'Muscat', 'is_default' => true]);

    actingAs($user)->post('/cart/add', ['variant_id' => $variant->id, 'quantity' => 1]);

    actingAs($user)->get('/checkout')->assertOk()->assertSee('1 Heritage Way');
});

it('saves the checkout address when the box is ticked', function () {
    Http::fake(['*/checkout/session' => Http::response(['data' => ['session_id' => 'sess_TEST']], 200)]);
    config([
        'services.thawani.base_url' => 'https://uatcheckout.thawani.om/api/v1',
        'services.thawani.pay_url' => 'https://uatcheckout.thawani.om/pay',
        'services.thawani.secret_key' => 'sk_test',
        'services.thawani.publishable_key' => 'pk_test',
        'services.thawani.mode' => 'uat',
    ]);
    $user = User::where('email', 'customer@example.com')->first();
    $variant = ProductVariant::where('stock_qty', '>', 0)->first();

    actingAs($user)->post('/cart/add', ['variant_id' => $variant->id, 'quantity' => 1]);
    actingAs($user)->post('/checkout', [
        'customer_name' => 'Buyer',
        'customer_email' => 'b@example.com',
        'customer_phone' => '+96890000000',
        'shipping_address_line1' => '7 New St',
        'shipping_city' => 'Muscat',
        'save_address' => '1',
    ]);

    expect($user->addresses()->where('line1', '7 New St')->exists())->toBeTrue();
});
