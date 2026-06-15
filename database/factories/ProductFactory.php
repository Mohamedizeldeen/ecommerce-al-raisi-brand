<?php

namespace Database\Factories;

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
        $nouns = ['Kaftan', 'Dress', 'Abaya', 'Jumpsuit', 'Tunic', 'Gown', 'Wrap Dress', 'Set', 'Blouse', 'Caftan'];
        $name = fake()->randomElement($adjectives).' '.fake()->randomElement($nouns);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
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
