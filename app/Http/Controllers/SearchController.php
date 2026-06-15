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
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('fabric', 'like', "%{$term}%");
            });
        }

        $products = $this->applyProductSort($query, $request)
            ->paginate(12)
            ->withQueryString();

        return view('search', compact('products', 'term'));
    }
}
