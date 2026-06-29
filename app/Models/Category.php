<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory, HasTranslations;

    /** @var list<string> */
    public array $translatable = ['name', 'description'];

    protected $guarded = ['id'];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    /**
     * The ProductType this evergreen category maps to, if its slug matches one
     * (e.g. "kaftans" -> ProductType::Kaftan). Lets a category landing page
     * auto-include every product of that type, not only those manually attached.
     */
    public function matchedProductType(): ?ProductType
    {
        return ProductType::fromSlug($this->slug);
    }

    /**
     * Base product query for this category's landing page: products attached to
     * this category or any of its sub-categories (pivot), unioned with products
     * carrying the matching product_type. Callers add ->published(), filters, etc.
     */
    public function productsQuery(): Builder
    {
        // Only active sub-categories contribute — deactivating a child hides both
        // its landing page and its products from the parent grid, consistently.
        $categoryIds = $this->children()->active()->pluck('id')->push($this->id);
        $type = $this->matchedProductType();

        return Product::query()->where(function (Builder $query) use ($categoryIds, $type) {
            $query->whereHas('categories', fn (Builder $q) => $q->whereIn('categories.id', $categoryIds));

            if ($type !== null) {
                $query->orWhere('product_type', $type->value);
            }
        });
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeRoots(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Cover image for category cards/headers: the uploaded cover if set, else a
     * deterministic free editorial image so cards always have a backdrop.
     * Mirrors Collection::coverImageUrl().
     */
    public function coverImageUrl(int $offset = 0): string
    {
        if (! empty($this->cover_image)) {
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
