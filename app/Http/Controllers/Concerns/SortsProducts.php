<?php

namespace App\Http\Controllers\Concerns;

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
}
