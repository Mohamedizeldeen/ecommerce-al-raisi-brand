<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Product extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    /** @var list<string> */
    public array $translatable = ['name', 'description', 'fabric', 'meta_title', 'meta_description'];

    protected $guarded = ['id'];

    protected $casts = [
        'specs' => 'array',
        'base_price_baisa' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class);
    }

    /**
     * "Style it with" — suggested matching pieces (complete-the-look).
     */
    public function pairings(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'product_pairings', 'product_id', 'paired_product_id')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getFormattedPriceAttribute(): string
    {
        return format_omr((int) $this->base_price_baisa);
    }

    public function getInStockAttribute(): bool
    {
        if (! $this->relationLoaded('variants')) {
            $this->load('variants');
        }

        return $this->variants->where('is_active', true)->sum('stock_qty') > 0;
    }

    public function primaryImageUrl(): ?string
    {
        return $this->getFirstMediaUrl('gallery') ?: null;
    }

    /** @return list<string> */
    public function galleryUrls(): array
    {
        return $this->getMedia('gallery')->map(fn ($media) => $media->getUrl())->all();
    }

    /**
     * Image for cards/lists: uploaded media if present, otherwise a deterministic
     * free placeholder image so the storefront never shows an empty tile.
     */
    public function displayImageUrl(int $offset = 0): string
    {
        return $this->primaryImageUrl() ?? $this->placeholderImageUrl($offset);
    }

    /** @return list<string> Product-page gallery: media, else three placeholders. */
    public function displayGalleryUrls(): array
    {
        $urls = $this->galleryUrls();

        return ! empty($urls)
            ? $urls
            : [$this->placeholderImageUrl(0), $this->placeholderImageUrl(1), $this->placeholderImageUrl(2)];
    }

    public function placeholderImageUrl(int $offset = 0): string
    {
        static $pool = null;

        if ($pool === null) {
            $files = glob(public_path('images/products/*.jpg')) ?: [];
            sort($files);
            $pool = array_map(fn ($file) => asset('images/products/'.basename($file)), $files);
        }

        if ($pool === []) {
            return 'https://picsum.photos/seed/'.$this->id.'/800/1000?grayscale';
        }

        return $pool[((int) $this->id + $offset) % count($pool)];
    }
}
