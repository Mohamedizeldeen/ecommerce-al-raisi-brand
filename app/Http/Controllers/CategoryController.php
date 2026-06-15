<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SortsProducts;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use SortsProducts;

    public function show(Category $category, Request $request)
    {
        abort_unless($category->is_active, 404);

        // Include products from this category and any of its sub-categories.
        $categoryIds = $category->children()->pluck('id')->push($category->id);

        $products = $this->applyProductSort(
            Product::published()
                ->with(['media', 'variants'])
                ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds)),
            $request
        )->paginate(12)->withQueryString();

        return view('categories.show', compact('category', 'products'));
    }
}
