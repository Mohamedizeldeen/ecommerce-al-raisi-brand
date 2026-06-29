<?php

namespace App\Http\Controllers;

use App\Enums\TagGroup;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Tag;

class HomeController extends Controller
{
    public function index()
    {
        // Category-first homepage (Search Demand Map): lead with the evergreen
        // product categories and the occasion layer, then editorial collections.
        $categories = Category::active()
            ->roots()
            ->orderBy('sort_order')
            ->with(['children' => fn ($q) => $q->active()])
            ->take(6)
            ->get();

        $occasions = Tag::active()
            ->group(TagGroup::Occasion)
            ->orderBy('sort_order')
            ->take(4)
            ->get();

        $featuredCollections = Collection::active()
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->take(3)
            ->get();

        $featuredProducts = Product::published()
            ->featured()
            ->with(['media', 'variants'])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        $newArrivals = Product::published()
            ->with(['media', 'variants'])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        return view('home', compact('categories', 'occasions', 'featuredCollections', 'featuredProducts', 'newArrivals'));
    }
}
