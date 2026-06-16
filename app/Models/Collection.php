<?php

namespace App\Models;

use App\Enums\CollectionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Collection extends Model
{
    use HasFactory, HasTranslations;

    /** @var list<string> */
    public array $translatable = ['name', 'description'];

    protected $guarded = ['id'];

    protected $casts = [
        'type' => CollectionType::class,
        'year' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Cover image: the uploaded cover if set, otherwise a deterministic free
     * editorial image so collection cards/headers always have a backdrop.
     */
    public function coverImageUrl(int $offset = 0): string
    {
        if ($this->cover_image) {
            return Str::startsWith($this->cover_image, ['http://', 'https://'])
                ? $this->cover_image
                : asset('storage/'.$this->cover_image);
        }

        static $pool = null;

        if ($pool === null) {
            $files = glob(public_path('images/collections/*.jpg')) ?: [];
            sort($files);
            $pool = array_map(fn ($file) => asset('images/collections/'.basename($file)), $files);
        }

        if ($pool === []) {
            return asset('images/heroes/hero.jpg');
        }

        return $pool[((int) $this->id + $offset) % count($pool)];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
