<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PressController extends Controller
{
    public function index()
    {
        $posts = Post::published()->type(Post::TYPE_PRESS)
            ->orderByRaw('published_at IS NULL, published_at DESC')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return view('press.index', compact('posts'));
    }

    public function show(Post $post)
    {
        abort_unless($post->type === Post::TYPE_PRESS, 404);
        abort_unless($post->is_active && (is_null($post->published_at) || $post->published_at->isPast()), 404);

        $related = Post::published()->type(Post::TYPE_PRESS)
            ->whereKeyNot($post->getKey())
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('press.show', compact('post', 'related'));
    }
}
