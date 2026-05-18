<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Organizer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name_en', 'name_ar', 'slug', 'bio_en', 'bio_ar',
        'logo', 'cover_image', 'website', 'email', 'phone',
        'social_media', 'verified', 'verification_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'verified' => 'boolean',
            'verification_date' => 'datetime',
            'social_media' => 'json',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Organizer $organizer) {
            if (empty($organizer->slug)) {
                $organizer->slug = Str::slug($organizer->name_en) . '-' . Str::random(6);
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

    public function promoCodes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PromoCode::class);
    }

    public function payouts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payout::class);
    }

    public function followers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Follow::class);
    }

    public function followerCount(): int
    {
        return $this->followers()->count();
    }

    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
