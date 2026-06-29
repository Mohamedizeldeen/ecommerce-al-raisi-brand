<?php

namespace Database\Factories;

use App\Enums\ProductType;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $adjectives = ['Eternal', 'Desert', 'Moonlit', 'Amber', 'Pearl', 'Dune', 'Oasis', 'Heritage', 'Mirage', 'Rosewater', 'Frankincense', 'Gilded', 'Twilight', 'Saffron'];

        // Pick a garment type first, then a noun consistent with it, so seeded
        // demo data carries a coherent product_type (SDM req. F).
        $byType = [
            ['type' => ProductType::Kaftan, 'noun' => 'Kaftan'],
            ['type' => ProductType::EveningDress, 'noun' => 'Gown'],
            ['type' => ProductType::MaxiDress, 'noun' => 'Maxi Dress'],
            ['type' => ProductType::Jumpsuit, 'noun' => 'Jumpsuit'],
            ['type' => ProductType::SetCoord, 'noun' => 'Co-ord Set'],
            ['type' => ProductType::Abaya, 'noun' => 'Abaya'],
            ['type' => ProductType::Jalabiya, 'noun' => 'Jalabiya'],
            ['type' => ProductType::ModestDress, 'noun' => 'Wrap Dress'],
        ];
        $pick = fake()->randomElement($byType);
        $name = fake()->randomElement($adjectives).' '.$pick['noun'];

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'product_type' => $pick['type'],
            'description' => fake()->paragraph(2),
            'fabric' => fake()->randomElement(['100% Pure Silk', '100% Printed Polyester', 'Cotton Blend', 'Linen', 'Crepe de Chine', 'Chiffon', 'Velvet']),
            'specs' => ['Care' => 'Dry clean only', 'Origin' => 'Made in Oman'],
            'base_price_baisa' => fake()->numberBetween(17, 325) * 1000,
            'is_active' => true,
            'is_featured' => fake()->boolean(25),
            'published_at' => now(),
        ];
    }
}
