<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneratedReport extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'date_start',
        'date_end',
        'aggregation',
        'categories',
        'result',
    ];

    protected $casts = [
        'categories' => 'array',
        'result' => 'array',
        'date_start' => 'date',
        'date_end' => 'date',
    ];

    protected $appends = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
