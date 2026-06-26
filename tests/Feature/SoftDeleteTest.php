<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('soft-deletes an order and can restore it', function () {
    $order = Order::create([
        'order_number' => 'AMAL-SD-1',
        'status' => OrderStatus::Processing,
        'payment_status' => PaymentStatus::Paid,
        'subtotal_baisa' => 1000,
        'total_baisa' => 1000,
        'currency' => 'OMR',
        'customer_name' => 'X',
        'customer_email' => 'x@example.com',
    ]);
    $id = $order->id;

    $order->delete();
    expect(Order::find($id))->toBeNull()
        ->and(Order::withTrashed()->find($id))->not->toBeNull()
        ->and(Order::withTrashed()->find($id)->trashed())->toBeTrue();

    Order::withTrashed()->find($id)->restore();
    expect(Order::find($id))->not->toBeNull();
});

it('soft-deleting a product removes it from the storefront but keeps the row', function () {
    $product = Product::factory()->create(['is_active' => true, 'published_at' => now()]);
    expect(Product::published()->whereKey($product->id)->exists())->toBeTrue();

    $product->delete();

    expect(Product::published()->whereKey($product->id)->exists())->toBeFalse()
        ->and(Product::find($product->id))->toBeNull()
        ->and(Product::withTrashed()->find($product->id))->not->toBeNull();
});

it('soft-deletes a variant so its row survives for order history and restock', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id, 'stock_qty' => 5, 'size' => 'M', 'color' => 'Red']);
    $id = $variant->id;

    $variant->delete();

    expect(ProductVariant::find($id))->toBeNull()
        ->and(ProductVariant::withTrashed()->find($id))->not->toBeNull()
        ->and(ProductVariant::withTrashed()->find($id)->trashed())->toBeTrue();
});
