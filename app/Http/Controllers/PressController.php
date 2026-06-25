<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PressController extends PostController
{
    protected function type(): string
    {
        return Post::TYPE_PRESS;
    }

    protected function viewPrefix(): string
    {
        return 'press';
    }
}
