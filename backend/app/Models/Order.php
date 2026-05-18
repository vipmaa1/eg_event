<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number', 'user_id', 'event_id',
        'customer_name', 'customer_email', 'customer_phone',
        'quantity', 'subtotal_amount', 'discount_amount',
        'fees_amount', 'total_amount', 'currency',
        'status', 'payment_method', 'payment_transaction_id',
        'payment_details', 'paid_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'fees_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'payment_details' => 'json',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(12));
            }
        });
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function markAsPaid(string $transactionId, string $method, array $details = []): void
    {
        $this->update([
            'status' => 'paid',
            'payment_transaction_id' => $transactionId,
            'payment_method' => $method,
            'payment_details' => $details,
            'paid_at' => now(),
        ]);
    }
}
