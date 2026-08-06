<?php

namespace App\Models;

use App\Enums\AdjustmentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'ingredient_id',
        'adjustment_type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reason',
        'reported_by',
        'adjusted_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'quantity_before' => 'decimal:3',
            'quantity_after' => 'decimal:3',
            'adjusted_at' => 'datetime',
            'adjustment_type' => AdjustmentType::class,
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (StockAdjustment $adjustment) {
            foreach ($adjustment->stockMovements as $movement) {
                $batch = $adjustment->ingredient?->batches()
                    ->where('id', $movement->ingredient_batch_id)
                    ->first();

                if ($batch) {
                    $batch->quantity += $movement->quantity_change;
                    $batch->save();
                }
            }
        });
    }
}
