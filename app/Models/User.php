<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $attributes = [
        'status' => 'active',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === UserRole::Admin && $this->isActive();
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => UserStatus::class,
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function cashierOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'cashier_id');
    }

    public function processedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'processed_by');
    }

    public function reportedStockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class, 'reported_by');
    }
}
