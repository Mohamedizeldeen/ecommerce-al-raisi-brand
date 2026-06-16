<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'subtotal_baisa' => 'integer',
        'shipping_baisa' => 'integer',
        'discount_baisa' => 'integer',
        'total_baisa' => 'integer',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Paid;
    }

    public function getFormattedTotalAttribute(): string
    {
        return format_omr((int) $this->total_baisa);
    }

    public static function generateNumber(): string
    {
        do {
            $number = 'AMAL-'.now()->format('Ymd').'-'.strtoupper(Str::random(8));
        } while (static::where('order_number', $number)->exists());

        return $number;
    }
}
