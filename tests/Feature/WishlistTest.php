<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('toggles a product in and out of the wishlist', function () {
    $user = User::where('email', 'customer@example.com')->first();
    $product = Product::factory()->create(['is_active' => true, 'published_at' => now()]);

    actingAs($user)->post(route('account.wishlist.toggle', $product))->assertRedirect();
    expect($user->wishlistProducts()->whereKey($product->id)->exists())->toBeTrue();

    actingAs($user)->post(route('account.wishlist.toggle', $product))->assertRedirect();
    expect($user->wishlistProducts()->whereKey($product->id)->exists())->toBeFalse();
});

it('shows saved products on the wishlist page', function () {
    $user = User::where('email', 'customer@example.com')->first();
    $product = Product::factory()->create(['is_active' => true, 'published_at' => now(), 'name' => 'Wished Item']);
    $user->wishlistProducts()->attach($product->id);

    actingAs($user)->get(route('account.wishlist'))->assertOk()->assertSee('Wished Item');
});

it('requires login to use the wishlist', function () {
    $product = Product::factory()->create();
    post(route('account.wishlist.toggle', $product))->assertRedirect(route('login'));
});
