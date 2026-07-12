<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuIngredient extends Model
{
    public $timestamps = false;

    protected $fillable = [
        "menu_id",
        "ingredient_id",
        "quantity_used",
        "unit_id",
    ];

    protected function casts(): array
    {
        return [
            "quantity_used" => "decimal:2",
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
