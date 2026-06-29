<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Shared index/show logic for the editorial sections (Blog, Press). Subclasses
 * only declare their post type and view namespace; "published" is defined in one
 * place (Post::scopePublished) so the listing and the show-guard can never drift.
 */
abstract class PostController extends Controller
{
    /** The Post::TYPE_* this section serves. */
    abstract protected function type(): string;

    /** The blade view namespace for this section (e.g. 'blog', 'press'). */
    abstract protected function viewPrefix(): string;

    /** The published posts for this section, in display order (unexecuted). */
    protected function publishedPostsQuery(): Builder
    {
        return Post::published()->type($this->type())
            ->orderByRaw('published_at IS NULL, published_at DESC')
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }

    /**
     * The published posts for this section, in display order.
     *
     * @return Collection<int, Post>
     */
    protected function publishedPosts(): Collection
    {
        return $this->publishedPostsQuery()->get();
    }

    public function index(): View
    {
        $posts = $this->publishedPosts();

        return view($this->viewPrefix().'.index', compact('posts'));
    }

    public function show(Post $post): View
    {
        // 404 unless the bound post is this section's type AND actually published —
        // checked through the single published() scope rather than a re-derived guard.
        abort_unless(
            Post::published()->type($this->type())->whereKey($post->getKey())->exists(),
            404
        );

        // "Shop this article" — only surface published products, in pivot order.
        $post->load(['products' => fn ($q) => $q->published()->with(['media', 'variants'])]);

        $related = Post::published()->type($this->type())
            ->whereKeyNot($post->getKey())
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view($this->viewPrefix().'.show', compact('post', 'related'));
    }
}
