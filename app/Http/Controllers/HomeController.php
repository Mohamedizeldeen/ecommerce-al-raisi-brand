<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $featuredCollections = Collection::active()
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->take(3)
            ->get();

        $featuredProducts = Product::published()
            ->featured()
            ->with(['media', 'variants'])
            ->latest('published_at')
            ->take(8)
            ->get();

        $newArrivals = Product::published()
            ->with(['media', 'variants'])
            ->latest('published_at')
            ->take(8)
            ->get();

        return view('home', compact('featuredCollections', 'featuredProducts', 'newArrivals'));
    }
}
