<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class Post extends Model
{
    use HasTranslations;

    public const TYPE_BLOG = 'blog';

    public const TYPE_PRESS = 'press';

    /** @var list<string> */
    public array $translatable = ['title', 'excerpt', 'body', 'meta_title', 'meta_description'];

    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Topic this article is filed under (SDM blog-category routing). */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    /** Products featured in this article — the "Shop this article" strip. */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** Active and either unscheduled or past its publish date. */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopeType(Builder $query, string $type): void
    {
        $query->where('type', $type);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Uploaded cover image (cache-busted) or null. */
    public function coverImageUrl(): ?string
    {
        if (! $this->cover_image) {
            return null;
        }

        return Str::startsWith($this->cover_image, ['http://', 'https://'])
            ? $this->cover_image
            : asset_version('storage/'.$this->cover_image);
    }

    /** Plain-text summary for cards/meta: excerpt if set, else trimmed body. */
    public function summary(int $length = 160): string
    {
        $text = (string) ($this->excerpt ?: strip_tags((string) $this->body));

        return Str::limit(trim($text), $length);
    }
}
