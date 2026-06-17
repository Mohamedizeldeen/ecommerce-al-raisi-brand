<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SortsProducts;
use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    use SortsProducts;

    public function index(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        $query = Product::published()->with(['media', 'variants']);

        if ($term !== '') {
            // Escape LIKE wildcards so user input is treated literally.
            $like = '%'.addcslashes($term, '%_\\').'%';
            $locale = app()->getLocale();

            // Translatable columns store JSON like {"en":"...","ar":"..."}.
            // Match the active-locale value via a portable JSON expression, and
            // fall back to the raw column so data lacking the active-locale key
            // (e.g. English-only content shown in the Arabic UI) still matches.
            $jsonLike = function ($q, string $column) use ($like, $locale) {
                $expr = "JSON_UNQUOTE(JSON_EXTRACT(`{$column}`, CONCAT('\$.\"', ?, '\"'))) LIKE ?";

                $q->whereRaw($expr, [$locale, $like])
                    ->orWhere($column, 'like', $like);
            };

            $query->where(function ($q) use ($jsonLike, $like) {
                $q->where(fn ($sub) => $jsonLike($sub, 'name'))
                    ->orWhere(fn ($sub) => $jsonLike($sub, 'description'))
                    ->orWhere(fn ($sub) => $jsonLike($sub, 'fabric'))
                    ->orWhereHas('categories', fn ($c) => $c->where('name', 'like', $like))
                    ->orWhereHas('collections', fn ($c) => $c->where('name', 'like', $like));
            });
        }

        // Order by relevance first, then honour the chosen storefront sort
        // (which defaults to published_at desc, id desc).
        $products = $this->applyProductSort(
            $query->orderByDesc('is_featured'),
            $request
        )->paginate(12)->withQueryString();

        return view('search', compact('products', 'term'));
    }
}
