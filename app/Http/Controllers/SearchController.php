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
            $escaped = addcslashes($term, '%_\\');

            $query->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                    ->orWhere('description', 'like', "%{$escaped}%")
                    ->orWhere('fabric', 'like', "%{$escaped}%");
            });
        }

        $products = $this->applyProductSort($query, $request)
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return view('search', compact('products', 'term'));
    }
}
