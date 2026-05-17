<?php

namespace App\Services;

use App\Models\MenuStock;
use App\Models\MenuStockAdjustment;
use App\Models\MenuStockMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MenuStockReconciliationService
{
    public function __construct(
        protected MenuStockService $menuStockService,
    ) {}

    public function createManualAdjustment(
        int $menuStockId,
        float $quantity,
        string $adjustmentType,
        string $reason,
        ?int $reportedBy = null,
        ?string $adjustedAt = null,
    ): MenuStockAdjustment {
        if ($quantity <= 0) {
            throw new RuntimeException('Jumlah penyesuaian harus lebih dari 0.');
        }

        if (! in_array($adjustmentType, MenuStockAdjustment::TYPES, true)) {
            throw new RuntimeException('Tipe penyesuaian tidak valid.');
        }

        return DB::transaction(function () use (
            $menuStockId,
            $quantity,
            $adjustmentType,
            $reason,
            $reportedBy,
            $adjustedAt,
        ) {
            $menuStock = MenuStock::with('batches')->findOrFail($menuStockId);
            $quantityBefore = (float) $menuStock->getTotalStock();

            if ($adjustmentType === MenuStockAdjustment::TYPE_DECREASE) {
                $signedQuantity = -$quantity;

                $adjustment = MenuStockAdjustment::create([
                    'menu_stock_id' => $menuStockId,
                    'adjustment_type' => $adjustmentType,
                    'quantity' => $signedQuantity,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityBefore,
                    'reason' => $reason,
                    'reported_by' => $reportedBy,
                    'adjusted_at' => $adjustedAt ?? now(),
                ]);

                $this->menuStockService->deductMenuStockBatch(
                    menuStockId: $menuStockId,
                    requiredQuantity: $quantity,
                    context: [
                        'movement_type' => 'adjustment_decrease',
                        'source_type' => 'menu_stock_adjustment',
                        'source_id' => (string) $adjustment->id,
                        'menu_stock_adjustment_id' => $adjustment->id,
                        'recorded_by' => $reportedBy,
                        'notes' => $reason,
                    ]
                );

                $quantityAfter = (float) MenuStock::findOrFail($menuStockId)->getTotalStock();

                $adjustment->update([
                    'quantity_after' => $quantityAfter,
                ]);

                return $adjustment;
            }

            $batch = $menuStock->batches()->orderByDesc('received_at')->first();

            if (! $batch) {
                throw new RuntimeException('Tidak ada batch untuk produk ini. Tambahkan batch terlebih dahulu.');
            }

            $batchBefore = (float) $batch->quantity;
            $batch->quantity = $batchBefore + $quantity;
            $batch->save();

            $quantityAfter = (float) MenuStock::findOrFail($menuStockId)->getTotalStock();

            $adjustment = MenuStockAdjustment::create([
                'menu_stock_id' => $menuStockId,
                'adjustment_type' => $adjustmentType,
                'quantity' => $quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reason' => $reason,
                'reported_by' => $reportedBy,
                'adjusted_at' => $adjustedAt ?? now(),
            ]);

            MenuStockMovement::create([
                'menu_stock_id' => $menuStockId,
                'menu_stock_batch_id' => $batch->id,
                'menu_stock_adjustment_id' => $adjustment->id,
                'movement_type' => 'adjustment_increase',
                'source_type' => 'menu_stock_adjustment',
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
