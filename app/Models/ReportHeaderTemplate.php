<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReportHeaderTemplate extends Model
{
    protected $fillable = [
        'name',
        'entity_name',
        'address',
        'phone',
        'additional_info',
        'is_default',
    ];

    protected $casts = [
        'additional_info' => 'array',
        'is_default' => 'boolean',
    ];

    public function scopeDefault(Builder $query): void
    {
        $query->where('is_default', true);
    }
}
