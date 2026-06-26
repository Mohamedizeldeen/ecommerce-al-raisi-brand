<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('accepts a review and holds it for moderation', function () {
    $product = Product::factory()->create(['is_active' => true, 'published_at' => now()]);

    post(route('products.reviews.store', $product), [
        'author_name' => 'Sara',
        'author_email' => 'sara@example.com',
        'rating' => 5,
        'title' => 'Lovely',
        'body' => 'Beautiful piece.',
    ])->assertSessionHas('success');

    $review = Review::first();
    expect($review)->not->toBeNull()
        ->and($review->is_approved)->toBeFalse()
        ->and($review->rating)->toBe(5)
        ->and($review->is_verified_purchase)->toBeFalse();
});

it('shows only approved reviews on the product page', function () {
    $product = Product::factory()->create(['is_active' => true, 'published_at' => now(), 'name' => 'Reviewed Piece']);
    Review::create(['product_id' => $product->id, 'author_name' => 'Approved Author', 'rating' => 5, 'body' => 'Great approved review.', 'is_approved' => true]);
    Review::create(['product_id' => $product->id, 'author_name' => 'Pending Author', 'rating' => 1, 'body' => 'Hidden pending review.', 'is_approved' => false]);

    get(route('products.show', $product))
        ->assertOk()
        ->assertSee('Approved Author')
        ->assertDontSee('Pending Author');
});

it('marks a review as a verified purchase when the reviewer bought the product', function () {
    $user = User::where('email', 'customer@example.com')->first();
    $product = Product::factory()->create(['is_active' => true, 'published_at' => now()]);

    $order = Order::create([
        'order_number' => 'AMAL-REV-1',
        'user_id' => $user->id,
        'status' => OrderStatus::Completed,
        'payment_status' => PaymentStatus::Paid,
        'subtotal_baisa' => 1000,
        'total_baisa' => 1000,
        'currency' => 'OMR',
        'customer_name' => 'Cust',
        'customer_email' => 'cust@example.com',
    ]);
    $order->items()->create([
        'product_id' => $product->id,
        'name' => 'X',
        'price_baisa' => 1000,
        'quantity' => 1,
        'line_total_baisa' => 1000,
    ]);

    actingAs($user)->post(route('products.reviews.store', $product), [
        'author_name' => 'Cust',
        'author_email' => 'cust@example.com',
        'rating' => 4,
        'body' => 'Bought and loved it.',
    ])->assertSessionHas('success');

    expect(Review::first()->is_verified_purchase)->toBeTrue();
});
