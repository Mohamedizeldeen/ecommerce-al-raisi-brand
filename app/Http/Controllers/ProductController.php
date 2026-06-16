<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'variants' => fn ($q) => $q->where('is_active', true)->orderBy('size')->orderBy('color'),
            'media',
            'categories',
            'collections',
            'pairings' => fn ($q) => $q->where('products.is_active', true)->with(['media', 'variants']),
        ]);

        // Avoid each variant lazy-loading its parent product (e.g. for in_stock checks).
        $product->variants->each->setRelation('product', $product);

        $related = Product::published()
            ->with(['media', 'variants'])
            ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $product->categories->pluck('id')))
            ->where('id', '!=', $product->id)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->take(4)
            ->get();

        return view('products.show', compact('product', 'related'));
    }
}
