<?php

use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
    $this->user = User::where('email', 'customer@example.com')->first();
    $this->variant = ProductVariant::where('stock_qty', '>', 0)->first();
});

it('breaks out inclusive VAT in the cart summary', function () {
    Setting::put('vat_percent', 5);
    actingAs($this->user)->post('/cart/add', ['variant_id' => $this->variant->id, 'quantity' => 1]);

    $summary = app(CartService::class)->summary();

    expect($summary['vat_percent'])->toBe(5)
        ->and($summary['tax'])->toBe((int) round($summary['total'] * 5 / 105))
        ->and($summary['tax'])->toBeGreaterThan(0);
});

it('respects the admin-configured VAT rate', function () {
    actingAs($this->user)->post('/cart/add', ['variant_id' => $this->variant->id, 'quantity' => 1]);

    Setting::put('vat_percent', 0);
    expect(app(CartService::class)->summary()['tax'])->toBe(0);

    Setting::put('vat_percent', 10);
    $summary = app(CartService::class)->summary();
    expect($summary['tax'])->toBe((int) round($summary['total'] * 10 / 110));
});

it('records VAT on the order at checkout', function () {
    Setting::put('vat_percent', 5);
    Http::fake(['*/checkout/session' => Http::response(['data' => ['session_id' => 'sess_TEST']], 200)]);
    config([
        'services.thawani.base_url' => 'https://uatcheckout.thawani.om/api/v1',
        'services.thawani.pay_url' => 'https://uatcheckout.thawani.om/pay',
        'services.thawani.secret_key' => 'sk_test',
        'services.thawani.publishable_key' => 'pk_test',
        'services.thawani.mode' => 'uat',
    ]);

    actingAs($this->user)->post('/cart/add', ['variant_id' => $this->variant->id, 'quantity' => 1]);
    actingAs($this->user)->post('/checkout', [
        'customer_name' => 'Buyer',
        'customer_email' => 'b@example.com',
        'customer_phone' => '+96890000000',
        'shipping_address_line1' => '1 Heritage Way',
        'shipping_city' => 'Muscat',
    ]);

    $order = Order::latest('id')->first();
    expect((int) $order->vat_percent)->toBe(5)
        ->and((int) $order->tax_baisa)->toBe((int) round($order->total_baisa * 5 / 105))
        ->and((int) $order->tax_baisa)->toBeGreaterThan(0);
});
