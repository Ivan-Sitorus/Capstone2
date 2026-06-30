<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
