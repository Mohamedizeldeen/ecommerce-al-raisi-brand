<?php

namespace App\Http\Controllers;

use App\Models\Post;

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
}
