<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataMiningRun extends Model
{
    protected $fillable = [
        'analysis_type', 'status', 'date_range_start', 'date_range_end',
        'parameters', 'preprocessing_logs', 'result', 'charts',
        'error_message', 'run_at', 'user_id',
    ];

    protected $casts = [
        'parameters' => 'array',
        'preprocessing_logs' => 'array',
        'result' => 'array',
        'charts' => 'array',
        'date_range_start' => 'date',
        'date_range_end' => 'date',
        'run_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOfType($query, string $type): void
    {
        $query->where('analysis_type', $type);
    }

    public function scopeCompleted($query): void
    {
        $query->where('status', 'completed');
    }

    public function scopeLatest($query): void
    {
        $query->latest('run_at');
    }
}
