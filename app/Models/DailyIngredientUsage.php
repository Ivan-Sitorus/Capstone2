<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyIngredientUsage extends Model
{
    protected $fillable = [
        'usage_date',
        'ingredient_id',
        'ingredient_name',
        'unit',
        'jumlah_digunakan',
    ];

    protected function casts(): array
    {
        return [
            'usage_date' => 'date',
            'jumlah_digunakan' => 'decimal:3',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
