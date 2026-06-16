<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SortsProducts;
use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    use SortsProducts;

    public function index()
    {
        $collections = Collection::active()
            ->withCount(['products' => fn ($q) => $q->published()])
            ->orderBy('sort_order')
            ->orderByDesc('year')
            ->get()
            ->groupBy(fn (Collection $c) => $c->type->getLabel());

        return view('collections.index', compact('collections'));
    }

    public function show(Collection $collection, Request $request)
    {
        abort_unless($collection->is_active, 404);

        $products = $this->applyProductSort(
            $collection->products()->published()->with(['media', 'variants']),
            $request
        )->paginate(12)->withQueryString();

        return view('collections.show', compact('collection', 'products'));
    }
}
