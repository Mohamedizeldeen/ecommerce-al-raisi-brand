<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ProductVariant;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait SortsProducts
{
    /**
     * Apply the storefront sort option (?sort=) to a product query.
     */
    protected function applyProductSort(Builder $query, Request $request): Builder
    {
        return match ($request->query('sort')) {
            'price_asc' => $query->orderBy('base_price_baisa'),
            'price_desc' => $query->orderByDesc('base_price_baisa'),
            default => $query->orderByDesc('published_at')->orderByDesc('id'),
        };
    }

    /**
     * Apply storefront facet filters to a product query: ?size=, ?color=,
     * ?min_price=, ?max_price= (OMR), ?in_stock=1.
     */
    protected function applyProductFilters(Builder $query, Request $request): Builder
    {
        if (($size = trim((string) $request->query('size', ''))) !== '') {
            $query->whereHas('variants', fn ($q) => $q->where('is_active', true)->where('size', $size));
        }

        if (($color = trim((string) $request->query('color', ''))) !== '') {
            $query->whereHas('variants', fn ($q) => $q->where('is_active', true)->where('color', $color));
        }

        if (is_numeric($request->query('min_price'))) {
            $query->where('base_price_baisa', '>=', (int) round(((float) $request->query('min_price')) * 1000));
        }

        if (is_numeric($request->query('max_price'))) {
            $query->where('base_price_baisa', '<=', (int) round(((float) $request->query('max_price')) * 1000));
        }

        if ($request->boolean('in_stock')) {
            $query->whereHas('variants', fn ($q) => $q->where('is_active', true)->where('stock_qty', '>', 0));
        }

        return $query;
    }

    /**
     * Distinct active size/colour options across a product set, for the filter UI.
     * Computed from the set BEFORE the size/colour filters are applied so options
     * don't disappear once a shopper narrows down.
     *
     * @return array{sizes: list<string>, colors: list<string>}
     */
    protected function productFacets(Builder $query): array
    {
        $productIds = (clone $query)->pluck('products.id');

        $variants = ProductVariant::query()
            ->whereIn('product_id', $productIds)
            ->where('is_active', true);

        return [
            'sizes' => (clone $variants)->whereNotNull('size')->where('size', '!=', '')
                ->distinct()->orderBy('size')->pluck('size')->all(),
            'colors' => (clone $variants)->whereNotNull('color')->where('color', '!=', '')
                ->distinct()->orderBy('color')->pluck('color')->all(),
        ];
    }
}
