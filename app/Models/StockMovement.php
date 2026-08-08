<?php

namespace App\Models;

use App\Enums\MovementType;
use App\Enums\SourceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class StockMovement extends Model
{
    use HasFactory;
    protected $fillable = [
        'ingredient_id',
        'ingredient_batch_id',
        'order_id',
        'order_item_id',
        'stock_adjustment_id',
        'movement_type',
        'source_type',
        'source_id',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'quantity_before' => 'decimal:3',
            'quantity_change' => 'decimal:3',
            'quantity_after' => 'decimal:3',
            'movement_type' => MovementType::class,
            'source_type' => SourceType::class,
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('Stock movements are immutable. Create a stock adjustment instead.');
        });

        static::deleting(function () {
            throw new LogicException('Stock movements are immutable. Create a stock adjustment instead.');
        });
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function ingredientBatch(): BelongsTo
    {
        return $this->belongsTo(IngredientBatch::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function stockAdjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class);
    }
}
