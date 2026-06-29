<?php

use App\Models\BlogCategory;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

it('shows the topic routing bar on the blog index', function () {
    get('/blog')
        ->assertOk()
        ->assertSee('Styling Guides')
        ->assertSee('Cultural Heritage');
});

it('filters blog articles by category', function () {
    get('/blog/category/styling-guides')
        ->assertOk()
        ->assertSee('How to Style a Kaftan for Eid')
        ->assertDontSee('The Omani Roots of Our Prints');
});

it('404s an inactive blog category', function () {
    BlogCategory::create(['name' => ['en' => 'Hidden'], 'slug' => 'hidden', 'is_active' => false]);

    get('/blog/category/hidden')->assertNotFound();
});

it('renders the Shop this article strip with linked products', function () {
    $post = Post::where('slug', 'styling-a-kaftan-for-eid')->firstOrFail();
    expect($post->products)->not->toBeEmpty();

    get('/blog/styling-a-kaftan-for-eid')
        ->assertOk()
        ->assertSee('Shop this article')
        ->assertSee($post->products->first()->name);
});

it('omits the Shop this article strip when no products are linked', function () {
    get('/blog/omani-roots-of-our-prints')
        ->assertOk()
        ->assertDontSee('Shop this article');
});

it('lists blog category pages in the sitemap', function () {
    get('/sitemap.xml')
        ->assertOk()
        ->assertSee('/blog/category/styling-guides');
});

it('orders linked products by their pivot sort order', function () {
    $post = Post::where('slug', 'styling-a-kaftan-for-eid')->firstOrFail();
    $sorts = $post->products->pluck('pivot.sort_order')->all();

    expect($sorts)->toBe([0, 1, 2]);
});
