<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActiveStaffSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_session_id',
        'user_id',
        'pin_verified_at',
        'active_context',
    ];

    protected function casts(): array
    {
        return [
            'pin_verified_at' => 'datetime',
        ];
    }

    public function deviceSession(): BelongsTo
    {
        return $this->belongsTo(DeviceSession::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereNotNull('pin_verified_at');
    }

    public function scopeForDevice(Builder $query, mixed $id): void
    {
        $query->where('device_session_id', $id);
    }

    public function resolveUser(): ?User
    {
        return $this->user;
    }
}
