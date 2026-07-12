<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionRule extends Model
{
    protected $fillable = [
        'promotion_id',
        'type',
        'value',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }
}
