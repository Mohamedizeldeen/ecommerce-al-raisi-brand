<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

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
}
