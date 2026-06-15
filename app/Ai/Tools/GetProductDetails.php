<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Returns full details for one product (by slug, taken from a prior search
 * result): every colour/size variant with its own stock and price, plus the
 * specification sheet. Use this to answer precise availability questions.
 */
class GetProductDetails implements Tool
{
    public function description(): string
    {
        return 'Get full details for a single product using the "slug" returned by '
            .'search_products. Returns every colour/size variant with its individual '
            .'stock level and price, plus specifications. Use this to answer precise '
            .'questions like "is the X available in size M / in blue?". Returns JSON, '
            .'or {"found": false} if the slug does not exist.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()
                ->description('The product slug from a previous search_products result.')
                ->required(),
        ];
    }

    public function handle(Request $request): string
    {
        $slug = trim((string) ($request['slug'] ?? ''));

        $product = Product::published()
            ->with(['variants', 'categories:id,name', 'collections:id,name'])
            ->where('slug', $slug)
            ->first();

        if (! $product) {
            return json_encode(['found' => false, 'note' => 'No product with slug "'.$slug.'".']);
        }

        $variants = $product->variants
            ->where('is_active', true)
            ->map(fn ($variant) => [
                'size' => $variant->size,
                'colour' => $variant->color,
                'in_stock' => $variant->stock_qty > 0,
                'stock_qty' => $variant->stock_qty,
                'price' => format_omr((int) ($variant->price_override_baisa ?? $product->base_price_baisa)),
            ])->values()->all();

        return json_encode([
            'found' => true,
            'name' => $product->name,
            'price' => $product->formatted_price,
            'in_stock' => $product->in_stock,
            'fabric' => $product->fabric,
            'description' => strip_tags((string) $product->description),
            'specifications' => $product->specs ?: null,
            'categories' => $product->categories->pluck('name')->all(),
            'collections' => $product->collections->pluck('name')->all(),
            'variants' => $variants,
            'url' => route('products.show', $product->slug),
        ], JSON_UNESCAPED_UNICODE);
    }
}
