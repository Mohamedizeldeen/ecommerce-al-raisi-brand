<?php

use App\Enums\ProductType;
use App\Enums\TagGroup;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('casts product_type to the ProductType enum and scopes by it', function () {
    $kaftan = Product::factory()->create(['product_type' => ProductType::Kaftan]);
    Product::factory()->create(['product_type' => ProductType::Abaya]);

    expect($kaftan->fresh()->product_type)->toBe(ProductType::Kaftan);
    expect(Product::ofType(ProductType::Kaftan)->pluck('id'))->toContain($kaftan->id);
    expect(Product::ofType(ProductType::Abaya)->pluck('id'))->not->toContain($kaftan->id);
});

it('exposes the SDM launch categories with stable slugs', function () {
    expect(ProductType::Kaftan->slug())->toBe('kaftans');
    expect(ProductType::EveningDress->slug())->toBe('evening-dresses');
    expect(ProductType::fromSlug('maxi-dresses'))->toBe(ProductType::MaxiDress);

    // SDM launch backbone is the 5 demand-validated families; Abaya/Jalabiya/
    // Modest Dresses are evergreen but not launch-backbone.
    expect(ProductType::launchCases())->toHaveCount(5)
        ->not->toContain(ProductType::Abaya);
    expect(ProductType::garmentCases())->toHaveCount(8)
        ->toContain(ProductType::Abaya)
        ->not->toContain(ProductType::Scarf);
});

it('excludes products of an inactive sub-category from the parent landing query', function () {
    $parent = Category::create(['name' => ['en' => 'Parent'], 'slug' => 'parent-x', 'is_active' => true]);
    $child = Category::create(['name' => ['en' => 'Child'], 'slug' => 'child-x', 'parent_id' => $parent->id, 'is_active' => false]);

    $product = Product::factory()->create();
    $product->categories()->attach($child->id);

    expect($parent->productsQuery()->published()->pluck('products.id'))->not->toContain($product->id);

    $child->update(['is_active' => true]);
    expect($parent->productsQuery()->published()->pluck('products.id'))->toContain($product->id);
});

it('tags products polymorphically and resolves the inverse relation', function () {
    $tag = Tag::create([
        'name' => ['en' => 'Wedding Guest', 'ar' => 'ضيفة العرس'],
        'slug' => 'wedding-guest',
        'group' => TagGroup::Occasion,
    ]);
    $product = Product::factory()->create();
    $product->tags()->attach($tag);

    expect($product->fresh()->tags)->toHaveCount(1);
    expect($tag->fresh()->group)->toBe(TagGroup::Occasion);
    expect($tag->products)->toHaveCount(1);
    expect($tag->getTranslation('name', 'ar'))->toBe('ضيفة العرس');
    expect($tag->getRouteKeyName())->toBe('slug');
});

it('finds products by occasion tag slug', function () {
    $tag = Tag::create([
        'name' => ['en' => 'Eid & Ramadan'],
        'slug' => 'eid-ramadan',
        'group' => TagGroup::Occasion,
    ]);
    $tagged = Product::factory()->create();
    $tagged->tags()->attach($tag);
    Product::factory()->create(); // untagged

    $found = Product::whereHas('tags', fn ($q) => $q->where('slug', 'eid-ramadan'))->get();

    expect($found)->toHaveCount(1);
    expect($found->first()->id)->toBe($tagged->id);
});
