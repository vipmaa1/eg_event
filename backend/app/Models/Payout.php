<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $fillable = [
        'organizer_id', 'event_id', 'amount', 'currency',
        'platform_fee', 'net_amount', 'status', 'method',
        'payment_details', 'transaction_id', 'paid_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'payment_details' => 'json',
            'paid_at' => 'datetime',
        ];
    }

    public function organizer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function event(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
