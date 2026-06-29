<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\Post;
use Illuminate\View\View;

class BlogController extends PostController
{
    protected function type(): string
    {
        return Post::TYPE_BLOG;
    }

    protected function viewPrefix(): string
    {
        return 'blog';
    }

    public function index(): View
    {
        return view('blog.index', [
            'posts' => $this->publishedPosts(),
            'categories' => BlogCategory::active()->orderBy('sort_order')->get(),
            'current' => null,
        ]);
    }

    /** SDM blog-category routing page: articles filed under one topic. */
    public function category(BlogCategory $category): View
    {
        abort_unless($category->is_active, 404);

        return view('blog.index', [
            'posts' => $this->publishedPostsQuery()->where('blog_category_id', $category->id)->get(),
            'categories' => BlogCategory::active()->orderBy('sort_order')->get(),
            'current' => $category,
        ]);
    }
}
