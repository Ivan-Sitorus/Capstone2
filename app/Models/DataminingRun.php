<?php

namespace App\Models;

use App\Enums\DataminingRunStatus;
use Illuminate\Database\Eloquent\Model;

class DataminingRun extends Model
{
    protected $fillable = [
        'type',
        'status',
        'parameters',
        'payload',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'payload'    => 'array',
        ];
    }

    public static function latestCompleted(string $type): ?self
    {
        return static::query()
            ->where('type', $type)
            ->where('status', DataminingRunStatus::Completed->value)
            ->latest()
            ->first();
    }

    public static function latest(string $type): ?self
    {
        return static::query()
            ->where('type', $type)
            ->latest()
            ->first();
    }

    public static function completedHistory(string $type, int $limit = 3): \Illuminate\Database\Eloquent\Collection
    {
        return static::query()
            ->where('type', $type)
            ->where('status', DataminingRunStatus::Completed->value)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
