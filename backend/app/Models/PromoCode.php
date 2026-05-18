<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PromoCode extends Model
{
    protected $fillable = [
        'code', 'event_id', 'organizer_id',
        'discount_type', 'discount_value',
        'usage_limit', 'usage_count', 'per_user_limit',
        'min_order_amount', 'valid_from', 'valid_until', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function event(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function organizer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function isValid(?float $orderAmount = null): bool
    {
        if (!$this->is_active) return false;
        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) return false;
        if ($this->valid_from->isFuture()) return false;
        if ($this->valid_until->isPast()) return false;
        if ($this->min_order_amount !== null && $orderAmount !== null && $orderAmount < $this->min_order_amount) return false;
        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->discount_type === 'percentage') {
            return round($amount * ($this->discount_value / 100), 2);
        }
        return min($this->discount_value, $amount);
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    public static function generateCode(): string
    {
        return strtoupper(Str::random(8));
    }
}
