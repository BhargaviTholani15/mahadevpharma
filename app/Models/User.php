<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role_id', 'full_name', 'email', 'phone', 'password',
        'otp_code', 'otp_expires_at', 'is_active', 'last_login_at',
    ];

    protected $hidden = ['password', 'otp_code'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'otp_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role->slug ?? null,
            'name' => $this->full_name,
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    public function isAdmin(): bool
    {
        return $this->role->slug === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role->slug === 'staff';
    }

    public function isClient(): bool
    {
        return $this->role->slug === 'client';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) return true;
        return $this->role->hasPermission($permission);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
