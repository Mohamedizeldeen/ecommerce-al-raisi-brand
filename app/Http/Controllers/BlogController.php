<?php

namespace App\Http\Controllers;

use App\Models\Post;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::published()->type(Post::TYPE_BLOG)
            ->orderByRaw('published_at IS NULL, published_at DESC')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return view('blog.index', compact('posts'));
    }

    public function show(Post $post)
    {
        abort_unless($post->type === Post::TYPE_BLOG, 404);
        abort_unless($post->is_active && (is_null($post->published_at) || $post->published_at->isPast()), 404);

        $related = Post::published()->type(Post::TYPE_BLOG)
            ->whereKeyNot($post->getKey())
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('blog.show', compact('post', 'related'));
    }
}
