<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockReconciliationService
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {
    }

    public function createManualAdjustment(
        int $ingredientId,
        float $quantity,
        string $adjustmentType,
        string $reason,
        ?int $reportedBy = null,
        ?string $adjustedAt = null,
    ): StockAdjustment {
        if ($quantity <= 0) {
            throw new RuntimeException('Jumlah penyesuaian harus lebih dari 0.');
        }

        if (! in_array($adjustmentType, StockAdjustment::TYPES, true)) {
            throw new RuntimeException('Tipe penyesuaian tidak valid.');
        }

        return DB::transaction(function () use (
            $ingredientId,
            $quantity,
            $adjustmentType,
            $reason,
            $reportedBy,
            $adjustedAt,
        ) {
            $ingredient = Ingredient::with('batches')->findOrFail($ingredientId);
            $quantityBefore = (float) $ingredient->getTotalStock();

            if ($adjustmentType === StockAdjustment::TYPE_DECREASE) {
                $signedQuantity = -$quantity;

                $adjustment = StockAdjustment::create([
                    'ingredient_id' => $ingredientId,
                    'adjustment_type' => $adjustmentType,
                    'quantity' => $signedQuantity,
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
                        'movement_type' => 'adjustment_decrease',
                        'source_type' => 'stock_adjustment',
                        'source_id' => (string) $adjustment->id,
                        'stock_adjustment_id' => $adjustment->id,
                        'recorded_by' => $reportedBy,
                        'notes' => $reason,
                    ]
                );

                $quantityAfter = (float) Ingredient::findOrFail($ingredientId)->getTotalStock();

                $adjustment->update([
                    'quantity_after' => $quantityAfter,
                ]);

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
                'ingredient_id' => $ingredientId,
                'adjustment_type' => $adjustmentType,
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
                'movement_type' => 'adjustment_increase',
                'source_type' => 'stock_adjustment',
                'source_id' => (string) $adjustment->id,
                'quantity_before' => $batchBefore,
                'quantity_change' => $quantity,
                'quantity_after' => (float) $batch->quantity,
                'unit_cost' => $batch->cost_per_unit,
                'notes' => $reason,
                'recorded_by' => $reportedBy,
            ]);

            return $adjustment;
        });
    }
}
