<?php

namespace Database\Factories;

use App\Enums\CollectionType;
use App\Models\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Collection>
 */
class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        $season = fake()->randomElement(['SS', 'AW']).fake()->numberBetween(20, 25);
        $name = $season.' Collection';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'season' => $season,
            'type' => CollectionType::Seasonal,
            'year' => 2000 + (int) substr($season, -2),
            'description' => fake()->sentence(12),
            'is_active' => true,
            'is_featured' => false,
        ];
    }
}
