<?php

use App\Mail\BackInStockMail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\artisan;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('captures a back-in-stock request for a sold-out variant', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true, 'stock_qty' => 0, 'size' => 'M', 'color' => 'Red']);

    post(route('stock.notify'), ['variant_id' => $variant->id, 'email' => 'Me@Example.com'])->assertRedirect();

    expect(StockNotification::where('product_variant_id', $variant->id)->where('email', 'me@example.com')->exists())->toBeTrue();
});

it('ignores a request for an in-stock variant', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true, 'stock_qty' => 5, 'size' => 'M', 'color' => 'Red']);

    post(route('stock.notify'), ['variant_id' => $variant->id, 'email' => 'me@example.com']);

    expect(StockNotification::count())->toBe(0);
});

it('emails waiting customers when stock returns', function () {
    Mail::fake();
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true, 'stock_qty' => 0, 'size' => 'M', 'color' => 'Red']);
    StockNotification::create(['product_variant_id' => $variant->id, 'email' => 'me@example.com']);

    $variant->update(['stock_qty' => 3]); // restocked

    artisan('stock:notify-restocked')->assertExitCode(0);

    Mail::assertQueued(BackInStockMail::class);
    expect(StockNotification::first()->notified_at)->not->toBeNull();
});
