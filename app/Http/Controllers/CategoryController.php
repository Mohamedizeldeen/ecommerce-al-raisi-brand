<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SortsProducts;
use App\Models\Category;
use App\Models\Collection;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use SortsProducts;

    /**
     * Evergreen product-category landing page, served at /collections/{slug}.
     * Falls back to a 301 redirect for legacy editorial-collection slugs so the
     * old /collections/{slug} URLs keep their SEO after the move to /lookbooks.
     */
    public function show(string $slug, Request $request)
    {
        $category = Category::with('parent')->where('slug', $slug)->first();

        if ($category === null) {
            if (Collection::where('slug', $slug)->exists()) {
                return redirect()->route('lookbooks.show', $slug, 301);
            }

            abort(404);
        }

        abort_unless($category->is_active, 404);

        $base = $category->productsQuery()->published()->with(['media', 'variants']);

        $facets = $this->productFacets($base);

        $products = $this->applyProductSort($this->applyProductFilters($base, $request), $request)
            ->paginate(12)->withQueryString();

        // Sub-categories render as their own SEO landing pages; surface them here
        // as links so the parent acts as a routing hub (SDM subcategory template).
        $children = $category->children()->active()->get();

        return view('categories.show', compact('category', 'products', 'facets', 'children'));
    }
}
