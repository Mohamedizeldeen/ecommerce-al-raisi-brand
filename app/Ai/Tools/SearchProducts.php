<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Searches the live product catalog. This is the assistant's only source of
 * truth for products — names, prices, materials, colours, sizes and stock.
 */
class SearchProducts implements Tool
{
    public function description(): string
    {
        return 'Search the Amal Al Raisi store catalogue for products by keyword '
            .'(matches product name, description, fabric, and category or collection name). '
            .'Use this for ANY question about which products exist, products in a category '
            .'or collection, their price, materials, available colours or sizes, and whether '
            .'they are in stock. Returns up to 8 matching products as JSON. If "results" is '
            .'empty, no such product exists in the store — do not invent one.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('Keywords to search for, e.g. "silk scarf", "kaftan", "abaya", "black dress".')
                ->required(),
            'in_stock_only' => $schema->boolean()
                ->description('When true, only return products that currently have stock.'),
        ];
    }

    public function handle(Request $request): string
    {
        $term = trim((string) ($request['query'] ?? ''));
        $inStockOnly = (bool) ($request['in_stock_only'] ?? false);

        if ($term === '') {
            return json_encode(['results' => [], 'note' => 'Empty search query.']);
        }

        $like = '%'.addcslashes($term, '%_\\').'%';

        $query = Product::published()
            ->with(['variants', 'categories:id,name', 'collections:id,name', 'media'])
            ->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('fabric', 'like', $like)
                    ->orWhereHas('categories', fn ($c) => $c->where('name', 'like', $like))
                    ->orWhereHas('collections', fn ($c) => $c->where('name', 'like', $like));
            });

        // Filter in-stock in SQL (before LIMIT) so an empty result truly means "none".
        if ($inStockOnly) {
            $query->whereHas('variants', fn ($v) => $v->where('is_active', true)->where('stock_qty', '>', 0));
        }

        $products = $query->orderByDesc('is_featured')->orderBy('sort_order')->limit(8)->get();

        if ($products->isEmpty()) {
            return json_encode([
                'results' => [],
                'note' => 'No products in the catalogue matched "'.$term.'".',
            ]);
        }

        $results = $products->map(fn (Product $product) => [
            'name' => $product->name,
            'price' => $product->formatted_price,
            'in_stock' => $product->in_stock,
            'fabric' => $product->fabric,
            'colours' => $product->variants->where('is_active', true)
                ->pluck('color')->filter()->unique()->values()->all(),
            'sizes' => $product->variants->where('is_active', true)
                ->pluck('size')->filter()->unique()->values()->all(),
            'categories' => $product->categories->pluck('name')->all(),
            'collections' => $product->collections->pluck('name')->all(),
            'description' => Str::limit(strip_tags((string) $product->description), 180),
            'slug' => $product->slug,
            'url' => route('products.show', $product->slug),
        ])->values()->all();

        return json_encode(['results' => $results], JSON_UNESCAPED_UNICODE);
    }
}
