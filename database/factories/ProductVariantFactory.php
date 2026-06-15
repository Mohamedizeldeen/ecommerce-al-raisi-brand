<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'sku' => 'AMR-'.strtoupper(Str::random(10)),
            'size' => fake()->randomElement(['S', 'M', 'L', 'XL']),
            'color' => fake()->randomElement(['Vanilla', 'Midnight', 'Desert Sand']),
            'color_hex' => fake()->randomElement(['#efe7d6', '#1f2330', '#d8c3a5']),
            'price_override_baisa' => null,
            'stock_qty' => fake()->numberBetween(0, 12),
            'is_active' => true,
        ];
    }
}
