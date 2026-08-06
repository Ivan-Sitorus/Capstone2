<?php

namespace App\Services;

use App\Enums\AdjustmentType;
use App\Enums\MovementType;
use App\Enums\SourceType;
use App\Models\Ingredient;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockReconciliationService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}
    public static function generateAdjustmentCode(): string
    {
        $dateKey = now()->format('dmy');
        $todayCount = \App\Models\StockAdjustment::whereDate('created_at', today())->count();
        return sprintf('ADJ-%s-%d', $dateKey, $todayCount + 1);
    }

    public function createManualAdjustment(
        ?int $ingredientId = null,
        float $quantity = 0,
        string $adjustmentType = '',
        ?string $reason = null,
        ?int $reportedBy = null,
        ?string $adjustedAt = null,
    ): StockAdjustment {
        if ($quantity <= 0) {
            throw new RuntimeException('Jumlah penyesuaian harus lebih dari 0.');
        }

        if (! in_array($adjustmentType, array_column(AdjustmentType::cases(), 'value'), true)) {
            throw new RuntimeException('Tipe penyesuaian tidak valid.');
        }

        return DB::transaction(function () use (
            $ingredientId, $quantity, $adjustmentType, $reason, $reportedBy, $adjustedAt,
        ) {
            $ingredient = Ingredient::with('batches')->findOrFail($ingredientId);
            $quantityBefore = (float) $ingredient->getTotalStock();

            if ($adjustmentType === AdjustmentType::Decrease->value) {
                $adjustment = StockAdjustment::create([
                    'code' => self::generateAdjustmentCode(),
                    'ingredient_id' => $ingredientId,
                    'adjustment_type' => AdjustmentType::Decrease,
                    'quantity' => -$quantity,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityBefore,
                    'reason' => $reason,
                    'reported_by' => $reportedBy,
                    'adjusted_at' => $adjustedAt ?? now(),
                ]);

                $this->inventoryService->decreaseStockForIngredient(
                    ingredientId: $ingredientId,
                    quantity: $quantity,
                    context: [
                        'movement_type' => MovementType::AdjustmentDecrease,
                        'source_type' => SourceType::StockAdjustment,
                        'source_id' => (string) $adjustment->id,
                        'stock_adjustment_id' => $adjustment->id,
                    ]
                );

                $quantityAfter = (float) Ingredient::findOrFail($ingredientId)->getTotalStock();
                $adjustment->update(['quantity_after' => $quantityAfter]);

                return $adjustment;
            }

            $batch = $ingredient->batches()->orderByDesc('received_at')->first();

            if (! $batch) {
                throw new RuntimeException('Tidak ada batch untuk bahan ini. Tambahkan batch terlebih dahulu.');
            }

            $batchBefore = (float) $batch->quantity;
            $batch->quantity = $batchBefore + $quantity;
            $batch->save();

            $quantityAfter = (float) Ingredient::findOrFail($ingredientId)->getTotalStock();

            $adjustment = StockAdjustment::create([
                'code' => self::generateAdjustmentCode(),
                'ingredient_id' => $ingredientId,
                'adjustment_type' => AdjustmentType::Increase,
                'quantity' => $quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reason' => $reason,
                'reported_by' => $reportedBy,
                'adjusted_at' => $adjustedAt ?? now(),
            ]);

            StockMovement::create([
                'ingredient_id' => $ingredientId,
                'ingredient_batch_id' => $batch->id,
                'stock_adjustment_id' => $adjustment->id,
                'movement_type' => MovementType::AdjustmentIncrease,
                'source_type' => SourceType::StockAdjustment,
                'source_id' => (string) $adjustment->id,
                'quantity_before' => $batchBefore,
                'quantity_change' => $quantity,
                'quantity_after' => (float) $batch->quantity,
                'unit_cost' => $batch->cost_per_unit,
            ]);

            return $adjustment;
        });
    }
}
