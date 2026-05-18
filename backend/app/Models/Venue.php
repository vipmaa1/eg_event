<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Venue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name_en', 'name_ar', 'slug', 'description_en', 'description_ar',
        'city', 'district', 'address_en', 'address_ar',
        'latitude', 'longitude', 'capacity', 'cover_image',
        'gallery', 'amenities', 'email', 'phone',
        'verified', 'verification_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'gallery' => 'json',
            'amenities' => 'json',
            'verified' => 'boolean',
            'verification_date' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Venue $venue) {
            if (empty($venue->slug)) {
                $venue->slug = Str::slug($venue->name_en) . '-' . Str::random(6);
            }
        });
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function scopeByCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
