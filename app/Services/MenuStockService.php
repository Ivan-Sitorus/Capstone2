<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuStock;
use App\Models\MenuStockBatch;
use App\Models\MenuStockMovement;
use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\DB;

class MenuStockService
{
    /**
     * Deduct stock from menu stock batches using FEFO/FIFO/Custom ordering.
     *
     * Mirrors InventoryService::deductIngredientStock() but for MenuStock (finished products).
     */
    public function deductMenuStockBatch(int $menuStockId, float $requiredQuantity, array $context = []): array
    {
        $menuStock = MenuStock::with('menu')->findOrFail($menuStockId);

        $query = MenuStockBatch::where('menu_stock_id', $menuStockId)
            ->where('quantity', '>', 0);

        switch ($menuStock->batch_mode) {
            case MenuStock::BATCH_MODE_FIFO:
                $query->orderByRaw('CASE WHEN received_at IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('received_at', 'asc')
                    ->orderBy('id', 'asc');
                break;
            case MenuStock::BATCH_MODE_CUSTOM:
                $query->orderByRaw('CASE WHEN custom_order IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('custom_order', 'asc')
                    ->orderBy('received_at', 'asc')
                    ->orderBy('id', 'asc');
                break;
            default: // FEFO (default)
                $query->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('expiry_date', 'asc')
                    ->orderBy('received_at', 'asc')
                    ->orderBy('id', 'asc');
        }

        $batches = $query->get();

        $totalAvailable = (float) $batches->sum('quantity');

        if ($totalAvailable < $requiredQuantity) {
            throw new Exception(
                "Stok menu tidak mencukupi untuk '{$menuStock->menu->name}'. " .
                "Dibutuhkan: {$requiredQuantity}, Tersedia: {$totalAvailable}"
            );
        }

        $remainingToDeduct = $requiredQuantity;
        $batchChanges = [];

        foreach ($batches as $batch) {
            if ($remainingToDeduct <= 0) {
                break;
            }

            $before = (float) $batch->quantity;
            $deductFromThisBatch = min($before, $remainingToDeduct);
            $after = $before - $deductFromThisBatch;

            $batch->quantity = $after;
            $batch->save();

            $remainingToDeduct -= $deductFromThisBatch;

            MenuStockMovement::create([
                'menu_stock_id' => $menuStockId,
                'menu_stock_batch_id' => $batch->id,
                'order_id' => $context['order_id'] ?? null,
                'order_item_id' => $context['order_item_id'] ?? null,
                'menu_stock_adjustment_id' => $context['menu_stock_adjustment_id'] ?? null,
                'movement_type' => $context['movement_type'] ?? 'sale',
                'source_type' => $context['source_type'] ?? null,
                'source_id' => isset($context['source_id']) ? (string) $context['source_id'] : null,
                'quantity_before' => $before,
                'quantity_change' => -$deductFromThisBatch,
                'quantity_after' => $after,
                'unit_cost' => $batch->cost_per_unit,
                'reference' => $context['reference'] ?? null,
                'notes' => $context['notes'] ?? null,
                'recorded_by' => $context['recorded_by'] ?? null,
            ]);

            $batchChanges[] = [
                'batch_id' => $batch->id,
                'deducted' => $deductFromThisBatch,
                'remaining' => $after,
            ];
        }

        return [
            'total_deducted' => $requiredQuantity,
            'batch_changes' => $batchChanges,
        ];
    }

    /**
     * Process all no-recipe menu items in an order, deducting from menu stock batches.
     *
     * Idempotent: skips if MenuStockMovement records already exist for this order.
     */
    public function processSaleForOrderMenuStock(Order $order, ?int $recordedBy = null): array
    {
        $alreadyProcessed = MenuStockMovement::query()
            ->where('order_id', $order->id)
            ->where('movement_type', 'sale')
            ->exists();

        if ($alreadyProcessed) {
            return [
                'success' => true,
                'message' => 'already processed',
                'skipped' => true,
            ];
        }

        $order->loadMissing('items.menu.menuIngredients', 'items.menu.menuStock.batches');

        return DB::transaction(function () use ($order, $recordedBy) {
            $changes = [];

            foreach ($order->items as $orderItem) {
                $menu = $orderItem->menu;

                // Only process no-recipe menus that have menu_stock
                if ($menu->menuIngredients->isNotEmpty() || ! $menu->menuStock) {
                    continue;
                }

                $context = [
                    'order_id' => $order->id,
                    'order_item_id' => $orderItem->id,
                    'movement_type' => 'sale',
                    'source_type' => 'order_item',
                    'source_id' => (string) $orderItem->id,
                    'recorded_by' => $recordedBy ?? $order->cashier_id,
                    'reference' => $order->order_code,
                ];

                $deduction = $this->deductMenuStockBatch(
                    menuStockId: $menu->menuStock->id,
                    requiredQuantity: (float) $orderItem->quantity,
                    context: $context,
                );

                $changes[] = [
                    'menu_stock_id' => $menu->menuStock->id,
                    'menu_name' => $menu->name,
                    'total_deducted' => (float) $orderItem->quantity,
                    'unit' => $menu->menuStock->unit,
                    'batches' => $deduction['batch_changes'],
                ];
            }

            if (empty($changes)) {
                return [
                    'success' => true,
                    'message' => 'No menu stock items to process',
                    'changes' => [],
                    'skipped' => true,
                ];
            }

            return [
                'success' => true,
                'message' => 'Stok menu berhasil dikurangi',
                'changes' => $changes,
            ];
        });
    }

    /**
     * Check if sufficient menu stock exists for a list of order items.
     *
     * Mirrors InventoryService::canFulfillOrder() but for MenuStock.
     */
    public function canFulfillOrderMenuStock(array $items): array
    {
        $insufficient = [];

        foreach ($items as $item) {
            $menu = Menu::with(['menuIngredients', 'menuStock.batches'])
                ->findOrFail($item['menu_id']);
            $quantity = (float) ($item['quantity'] ?? 0);

            if ($quantity <= 0 || $menu->menuIngredients->isNotEmpty() || ! $menu->menuStock) {
                continue;
            }

            $availableQuantity = $menu->menuStock->getTotalStock();

            if ($availableQuantity < $quantity) {
                $insufficient[] = [
                    'menu_name' => $menu->name,
                    'required' => $quantity,
                    'available' => $availableQuantity,
                    'unit' => $menu->menuStock->unit,
                ];
            }
        }

        return [
            'can_fulfill' => empty($insufficient),
            'insufficient_items' => $insufficient,
        ];
    }
}
