<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    protected $fillable = [
        'event_id', 'name_en', 'name_ar',
        'description_en', 'description_ar',
        'price', 'currency', 'quantity_total', 'quantity_sold',
        'sales_start', 'sales_end',
        'max_per_order', 'min_per_order', 'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sales_start' => 'datetime',
            'sales_end' => 'datetime',
        ];
    }

    public function event(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available'
            && $this->quantity_sold < $this->quantity_total
            && ($this->sales_start === null || $this->sales_start->isPast())
            && ($this->sales_end === null || $this->sales_end->isFuture());
    }

    public function hasCapacityFor(int $quantity): bool
    {
        return ($this->quantity_total - $this->quantity_sold) >= $quantity;
    }
}
