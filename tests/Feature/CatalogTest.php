<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('flags a product on sale only when compare-at exceeds the price', function () {
    $p = Product::factory()->create(['base_price_baisa' => 50000, 'compare_at_price_baisa' => 80000]);
    expect($p->onSale())->toBeTrue();

    $p->update(['compare_at_price_baisa' => 40000]);
    expect($p->fresh()->onSale())->toBeFalse();

    $p->update(['compare_at_price_baisa' => null]);
    expect($p->fresh()->onSale())->toBeFalse();
});

it('shows a Sale badge on a discounted product card', function () {
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create([
        'is_active' => true,
        'published_at' => now(),
        'name' => 'Discounted Piece',
        'base_price_baisa' => 50000,
        'compare_at_price_baisa' => 90000,
    ]);
    $product->categories()->attach($category);
    // In stock so the Sale badge shows (Sold Out takes priority on the card).
    ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true, 'stock_qty' => 5]);

    get(route('categories.show', $category))
        ->assertOk()
        ->assertSee('Sale')
        ->assertSee('Discounted Piece');
});

it('filters a listing to in-stock products only', function () {
    $category = Category::factory()->create(['is_active' => true]);
    $visible = Product::factory()->create(['is_active' => true, 'published_at' => now(), 'name' => 'Visible Piece']);
    $hidden = Product::factory()->create(['is_active' => true, 'published_at' => now(), 'name' => 'Hidden Piece']);
    $visible->categories()->attach($category);
    $hidden->categories()->attach($category);
    ProductVariant::factory()->create(['product_id' => $visible->id, 'is_active' => true, 'stock_qty' => 5, 'size' => 'M', 'color' => 'Red']);
    ProductVariant::factory()->create(['product_id' => $hidden->id, 'is_active' => true, 'stock_qty' => 0, 'size' => 'L', 'color' => 'Blue']);

    get(route('categories.show', $category).'?in_stock=1')
        ->assertOk()
        ->assertSee('Visible Piece')
        ->assertDontSee('Hidden Piece');
});

it('filters a listing by size', function () {
    $category = Category::factory()->create(['is_active' => true]);
    $product = Product::factory()->create(['is_active' => true, 'published_at' => now(), 'name' => 'Sized Piece']);
    $product->categories()->attach($category);
    ProductVariant::factory()->create(['product_id' => $product->id, 'is_active' => true, 'stock_qty' => 3, 'size' => 'M', 'color' => 'Red']);

    get(route('categories.show', $category).'?size=M')->assertOk()->assertSee('Sized Piece');
    get(route('categories.show', $category).'?size=XXL')->assertOk()->assertDontSee('Sized Piece');
});
