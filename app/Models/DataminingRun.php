<?php

namespace App\Models;

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
}
