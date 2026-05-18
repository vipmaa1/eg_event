<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_code', 'order_id', 'event_id', 'ticket_type_id',
        'user_id', 'price_paid', 'currency',
        'holder_name', 'holder_email', 'holder_phone',
        'qr_code_url', 'status', 'checked_in',
        'checked_in_at', 'checked_in_by', 'staff_notes',
    ];

    protected function casts(): array
    {
        return [
            'price_paid' => 'decimal:2',
            'checked_in' => 'boolean',
            'checked_in_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            if (empty($ticket->ticket_code)) {
                $ticket->ticket_code = 'TKT-' . strtoupper(Str::random(16));
            }
        });
    }

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function event(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkedInByUser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function isValid(): bool
    {
        return $this->status === 'valid' && !$this->checked_in;
    }

    public function markAsUsed(int $checkedInBy): void
    {
        $this->update([
            'status' => 'used',
            'checked_in' => true,
            'checked_in_at' => now(),
            'checked_in_by' => $checkedInBy,
        ]);
    }

    public function scopeValid($query)
    {
        return $query->where('status', 'valid');
    }
}
