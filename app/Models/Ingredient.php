<?php

namespace App\Models;

use App\Enums\BatchMode;
use App\Enums\Unit;
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
    ];

    protected function casts(): array
    {
        return [
            'low_stock_threshold' => 'decimal:3',
            'unit' => Unit::class,
            'batch_mode' => BatchMode::class,
        ];
    }

    public static function batchModes(): array
    {
        return [
            BatchMode::Fefo->value => 'FEFO (First Expired First Out)',
            BatchMode::Fifo->value => 'FIFO (First In First Out)',
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
