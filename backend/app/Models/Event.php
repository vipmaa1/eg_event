<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organizer_id', 'venue_id', 'created_by_user_id',
        'title_en', 'title_ar', 'slug',
        'description_en', 'description_ar',
        'start_date', 'end_date', 'category',
        'cover_image', 'gallery', 'tags',
        'status', 'has_tickets', 'is_free',
        'view_count', 'attending_count', 'save_count',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'gallery' => 'json',
            'tags' => 'json',
            'has_tickets' => 'boolean',
            'is_free' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (empty($event->slug)) {
                $base = Str::slug($event->title_en);
                $slug = $base;
                $count = 2;
                while (Event::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $count++;
                }
                $event->slug = $slug;
            }
        });
    }

    public function organizer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Organizer::class);
    }

    public function venue(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function ticketTypes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function tickets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function comments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function userActions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserEventAction::class);
    }

    public function promoCodes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PromoCode::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now());
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title_en', 'like', "%{$term}%")
              ->orWhere('title_ar', 'like', "%{$term}%")
              ->orWhere('description_en', 'like', "%{$term}%")
              ->orWhere('description_ar', 'like', "%{$term}%");
        });
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function cheapestTicket(): ?TicketType
    {
        return $this->ticketTypes()
            ->where('status', 'available')
            ->orderBy('price')
            ->first();
    }

    public function isUpcoming(): bool
    {
        return $this->start_date->isFuture();
    }
}
