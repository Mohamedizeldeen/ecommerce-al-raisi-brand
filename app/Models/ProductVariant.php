<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'price_override_baisa' => 'integer',
        'stock_qty' => 'integer',
        'weight_grams' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Effective unit price in baisa: override, else the product's base price. */
    public function getPriceBaisaAttribute(): int
    {
        return (int) ($this->price_override_baisa ?? $this->product->base_price_baisa);
    }

    public function getFormattedPriceAttribute(): string
    {
        return format_omr($this->price_baisa);
    }

    public function getLabelAttribute(): string
    {
        return collect([$this->size, $this->color])->filter()->implode(' / ') ?: 'Default';
    }

    public function getInStockAttribute(): bool
    {
        return $this->is_active && $this->stock_qty > 0;
    }

    /** Uploaded photo for this colour/variant, cache-busted, or null. */
    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Str::startsWith($this->image_path, ['http://', 'https://'])
            ? $this->image_path
            : asset_version('storage/'.$this->image_path);
    }
}
