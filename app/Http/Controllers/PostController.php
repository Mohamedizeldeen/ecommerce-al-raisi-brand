<?php

namespace App\Http\Controllers;

use App\Models\Post;
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

    public function index(): View
    {
        $posts = Post::published()->type($this->type())
            ->orderByRaw('published_at IS NULL, published_at DESC')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

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

        $related = Post::published()->type($this->type())
            ->whereKeyNot($post->getKey())
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view($this->viewPrefix().'.show', compact('post', 'related'));
    }
}
