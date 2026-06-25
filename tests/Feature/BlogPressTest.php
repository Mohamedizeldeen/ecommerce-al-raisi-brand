<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed());

function makePost(array $attrs = []): Post
{
    return Post::create(array_merge([
        'type' => Post::TYPE_BLOG,
        'slug' => 'a-post',
        'title' => 'A Post Title',
        'excerpt' => 'A short excerpt.',
        'body' => 'The full body.',
        'is_active' => true,
        'published_at' => now()->subDay(),
        'sort_order' => 0,
    ], $attrs));
}

it('lists and shows a published blog post', function () {
    makePost(['slug' => 'heritage-notes', 'title' => 'Heritage Notes']);

    get('/blog')->assertOk()->assertSee('Heritage Notes');
    get('/blog/heritage-notes')->assertOk()->assertSee('Heritage Notes');
});

it('404s an unpublished blog post', function () {
    makePost(['slug' => 'draft', 'is_active' => false]);

    get('/blog/draft')->assertNotFound();
});

it('404s a future-dated (scheduled) blog post', function () {
    makePost(['slug' => 'scheduled', 'published_at' => now()->addWeek()]);

    get('/blog/scheduled')->assertNotFound();
});

it('404s when a post is requested under the wrong section', function () {
    makePost(['type' => Post::TYPE_PRESS, 'slug' => 'press-release', 'title' => 'Press Release']);

    // A press post must not resolve under /blog, and vice versa.
    get('/blog/press-release')->assertNotFound();
    get('/press/press-release')->assertOk()->assertSee('Press Release');

    makePost(['type' => Post::TYPE_BLOG, 'slug' => 'blog-story', 'title' => 'Blog Story']);
    get('/press/blog-story')->assertNotFound();
});
