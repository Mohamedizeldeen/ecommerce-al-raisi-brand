<?php

namespace App\Http\Controllers;

use App\Enums\TagGroup;
use App\Models\Category;
use App\Models\Tag;

class ShopController extends Controller
{
    /**
     * Shop hub (SDM "Shop Template — Parent"): a routing page to the evergreen
     * primary categories, plus the occasion layer.
     */
    public function index()
    {
        $categories = Category::active()
            ->roots()
            ->orderBy('sort_order')
            ->with(['children' => fn ($q) => $q->active()])
            ->get();

        $occasions = Tag::active()
            ->group(TagGroup::Occasion)
            ->orderBy('sort_order')
            ->get();

        return view('shop.index', compact('categories', 'occasions'));
    }
}
