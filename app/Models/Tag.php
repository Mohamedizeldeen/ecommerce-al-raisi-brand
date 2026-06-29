<?php

namespace App\Models;

use App\Enums\TagGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Tag extends Model
{
    use HasFactory, HasTranslations;

    /** @var list<string> */
    public array $translatable = ['name', 'description'];

    protected $guarded = ['id'];

    protected $casts = [
        'group' => TagGroup::class,
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'taggable');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeGroup(Builder $query, TagGroup|string $group): void
    {
        $query->where('group', $group instanceof TagGroup ? $group->value : $group);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Cover image: the uploaded cover if set, otherwise a deterministic free
     * editorial image so occasion cards/headers always have a backdrop.
     * Mirrors Collection::coverImageUrl().
     */
    public function coverImageUrl(int $offset = 0): string
    {
        if ($this->cover_image) {
            return Str::startsWith($this->cover_image, ['http://', 'https://'])
                ? $this->cover_image
                : asset_version('storage/'.$this->cover_image);
        }

        static $pool = null;

        if ($pool === null) {
            $files = glob(public_path('images/collections/*.jpg')) ?: [];
            sort($files);
            $pool = array_map(fn ($file) => asset_version('images/collections/'.basename($file)), $files);
        }

        if ($pool === []) {
            return asset_version('images/heroes/hero.jpg');
        }

        return $pool[((int) $this->id + $offset) % count($pool)];
    }
}
