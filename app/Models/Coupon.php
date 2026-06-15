<?php

namespace App\Models;

use App\Enums\CouponType;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'type' => CouponType::class,
        'value' => 'integer',
        'min_total_baisa' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
    ];

    public function isValidFor(int $subtotalBaisa): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return $subtotalBaisa >= $this->min_total_baisa;
    }

    public function discountFor(int $subtotalBaisa): int
    {
        if (! $this->isValidFor($subtotalBaisa)) {
            return 0;
        }

        return $this->type === CouponType::Percent
            ? (int) floor($subtotalBaisa * $this->value / 100)
            : min((int) $this->value, $subtotalBaisa);
    }
}
