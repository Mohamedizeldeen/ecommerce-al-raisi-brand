<?php

namespace App\Http\Controllers;

use App\Enums\TagGroup;
use App\Http\Controllers\Concerns\SortsProducts;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;

class OccasionController extends Controller
{
    use SortsProducts;

    /**
     * Occasions hub (SDM "Occasions Hub Template"): a visual routing menu linking
     * to each specific occasion page.
     */
    public function index()
    {
        $occasions = Tag::active()
            ->group(TagGroup::Occasion)
            ->orderBy('sort_order')
            ->withCount(['products' => fn ($q) => $q->published()])
            ->get();

        return view('occasions.index', compact('occasions'));
    }

    /**
     * Specific occasion page (SDM "Specific Occasion Template"): the destination
     * product grid for a search-driven event, populated by the occasion tag.
     */
    public function show(Tag $tag, Request $request)
    {
        abort_unless($tag->is_active && $tag->group === TagGroup::Occasion, 404);

        $base = $tag->products()->published()->with(['media', 'variants']);

        $facets = $this->productFacets($base);

        $products = $this->applyProductSort($this->applyProductFilters($base, $request), $request)
            ->paginate(12)->withQueryString();

        return view('occasions.show', compact('tag', 'products', 'facets'));
    }
}
