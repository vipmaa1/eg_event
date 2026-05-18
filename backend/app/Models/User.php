<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $fillable = [
        'email',
        'password',
        'phone',
        'name',
        'profile_photo',
        'bio',
        'status',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'preferences' => 'json',
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
        ];
    }

    public function roles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('role', $role)->where('is_active', true)->exists();
    }

    public function assignRole(string $role): void
    {
        $this->roles()->updateOrCreate(
            ['role' => $role],
            ['is_active' => true]
        );
    }

    public function organizer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Organizer::class);
    }

    public function venues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Venue::class);
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

    public function follows(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Follow::class);
    }

    public function eventActions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserEventAction::class);
    }

    public function getRolesListAttribute(): Collection
    {
        return $this->roles()->where('is_active', true)->pluck('role');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
