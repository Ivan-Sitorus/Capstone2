<?php

namespace App\Models;

use App\Enums\AdjustmentCategory;
use App\Enums\AdjustmentType;
use App\Enums\AdjustableType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'adjustable_type',
        'ingredient_id',
        'menu_id',
        'adjustment_type',
        'category',
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
            'adjustable_type' => AdjustableType::class,
            'adjustment_type' => AdjustmentType::class,
            'category' => AdjustmentCategory::class,
        ];
    }

    public static function getCategoryOptions(?string $type): array
    {
        return match ($type) {
            AdjustmentType::Increase->value => [
                AdjustmentCategory::Correction->value => 'Koreksi Stok',
                AdjustmentCategory::Other->value => 'Lainnya',
            ],
            AdjustmentType::Decrease->value => [
                AdjustmentCategory::Expired->value => 'Kedaluwarsa',
                AdjustmentCategory::Damaged->value => 'Rusak',
                AdjustmentCategory::Spilled->value => 'Tumpah',
                AdjustmentCategory::Complaint->value => 'Komplain Pelanggan',
                AdjustmentCategory::Correction->value => 'Koreksi Stok',
                AdjustmentCategory::Other->value => 'Lainnya',
            ],
            default => [],
        };
    }

    public function isIngredientAdjustment(): bool
    {
        return $this->adjustable_type === AdjustableType::Ingredient;
    }

    public function isMenuAdjustment(): bool
    {
        return $this->adjustable_type === AdjustableType::Menu;
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
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
