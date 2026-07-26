<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'unit',
        'low_stock_threshold',
        'batch_mode',
        'unit_id',
    ];

    const UNITS = [
        'gram' => 'Gram (g)',
        'kg' => 'Kilogram (kg)',
        'ml' => 'Mililiter (ml)',
        'liter' => 'Liter (L)',
        'pcs' => 'Buah / Pcs',
        'sachet' => 'Sachet',
    ];

    const BATCH_MODE_FIFO = 'fifo';
    const BATCH_MODE_FEFO = 'fefo';
    public static function batchModes(): array
    {
        return [
            self::BATCH_MODE_FEFO => 'FEFO (First Expired First Out)',
            self::BATCH_MODE_FIFO => 'FIFO (First In First Out)',
        ];
    }

    public function batches(): HasMany
    {
        return $this->hasMany(IngredientBatch::class);
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'menu_ingredients')
            ->withPivot('quantity_used');
    }

    public function menuIngredients(): HasMany
    {
        return $this->hasMany(MenuIngredient::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function getTotalStock(): float
    {
        return (float) $this->batches()->sum('quantity');
    }
}
