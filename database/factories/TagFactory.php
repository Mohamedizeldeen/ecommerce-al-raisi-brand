<?php

namespace Database\Factories;

use App\Enums\TagGroup;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'group' => fake()->randomElement(TagGroup::cases()),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function occasion(): static
    {
        return $this->state(fn () => ['group' => TagGroup::Occasion]);
    }
}
