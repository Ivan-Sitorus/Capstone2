<?php

namespace App\Services;

use App\Enums\BatchMode;
use App\Enums\MovementType;
use App\Enums\SourceType;
use App\Models\DailyIngredientUsage;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\Order;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    public function processSaleForOrder(Order $order): array
    {
        $alreadyProcessed = StockMovement::query()
            ->where('order_id', $order->id)
            ->where('movement_type', MovementType::Sale)
            ->exists();

        if ($alreadyProcessed) {
            return [
                'success' => true,
                'message' => 'Stok penjualan untuk order ini sudah diproses sebelumnya',
                'changes' => [],
                'skipped' => true,
            ];
        }

        $order->loadMissing('items');

        $items = $order->items
            ->map(function ($orderItem) use ($order) {
                return [
                    'menu_id' => (int) $orderItem->menu_id,
                    'quantity' => (int) $orderItem->quantity,
                    'order_id' => $order->id,
                    'order_item_id' => $orderItem->id,
                    'reference' => $order->order_code,
                    'usage_date' => $order->created_at?->toDateString(),
                ];
            })
            ->all();

        if (empty($items)) {
            return [
                'success' => true,
                'message' => 'Order tidak memiliki item untuk diproses',
                'changes' => [],
                'skipped' => true,
            ];
        }

        return $this->decreaseStockForOrder($items);
    }

    public function decreaseStockForOrder(array $items): array
    {
        return DB::transaction(function () use ($items) {
            $stockChanges = [];

            // Pre-load all menus in one query to avoid N+1
            $menuIds = array_unique(array_column($items, 'menu_id'));
            $menus = Menu::with(['menuIngredients.ingredient'])
                ->whereIn('id', $menuIds)
                ->get()
                ->keyBy('id');

            foreach ($items as $item) {
                $menu = $menus->get($item['menu_id']) ?? Menu::with('menuIngredients.ingredient')->findOrFail($item['menu_id']);
                $quantity = (int) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    throw new \InvalidArgumentException(
                        "Jumlah pesanan tidak valid untuk menu '{$menu->name}': quantity harus lebih dari 0"
                    );
                }

                $itemContext = [
                    'movement_type' => MovementType::Sale,
                    'source_type' => SourceType::OrderItem,
                    'source_id' => isset($item['order_item_id']) ? (string) $item['order_item_id'] : null,
                    'order_id' => $item['order_id'] ?? null,
                    'order_item_id' => $item['order_item_id'] ?? null,
                    'reference' => $item['reference'] ?? null,
                    'usage_date' => $item['usage_date'] ?? null,
                ];

                if ($menu->menuIngredients->isNotEmpty()) {
                    foreach ($menu->menuIngredients as $menuIngredient) {
                        $ingredient = $menuIngredient->ingredient;
                        $requiredQuantity = (float) $menuIngredient->quantity_used * $quantity;

                        $deduction = $this->deductIngredientStock(
                            ingredient: $ingredient,
                            requiredQuantity: $requiredQuantity,
                            context: $itemContext
                        );

                        $stockChanges[] = [
                            'ingredient_id' => $ingredient->id,
                            'ingredient_name' => $ingredient->name,
                            'total_deducted' => $requiredQuantity,
                            'unit' => $ingredient->unit,
                            'batches' => $deduction['batch_changes'],
                        ];
                    }
                }
            }

            return [
                'success' => true,
                'message' => 'Stok berhasil dikurangi',
                'changes' => $stockChanges,
            ];
        });
    }

    public function decreaseStockForIngredient(int $ingredientId, float $quantity, array $context = []): array
    {
        $ingredient = Ingredient::findOrFail($ingredientId);
        return DB::transaction(function () use ($ingredient, $quantity, $context) {
            return $this->deductIngredientStock($ingredient, $quantity, $context);
        });
    }

    public function canFulfillOrder(array $items): array
    {
        $insufficient = [];

        foreach ($items as $item) {
            $menu = Menu::with(['menuIngredients.ingredient'])->findOrFail($item['menu_id']);
            $quantity = (int) ($item['quantity'] ?? 0);

            if ($quantity <= 0) {
                throw new \InvalidArgumentException(
                    "Jumlah pesanan tidak valid untuk menu '{$menu->name}': quantity harus lebih dari 0"
                );
            }

            if ($menu->menuIngredients->isNotEmpty()) {
                foreach ($menu->menuIngredients as $menuIngredient) {
                    $ingredient = $menuIngredient->ingredient;
                    $requiredQuantity = (float) $menuIngredient->quantity_used * $quantity;

                    $availableQuantity = $ingredient->getTotalStock();

                    if ($availableQuantity < $requiredQuantity) {
                        $insufficient[] = [
                            'ingredient_name' => $ingredient->name,
                            'required' => $requiredQuantity,
                            'available' => $availableQuantity,
                            'unit' => $ingredient->unit,
                        ];
                    }
                }
            }
        }

        return [
            'can_fulfill' => empty($insufficient),
            'insufficient_ingredients' => $insufficient,
        ];
    }

    private function deductIngredientStock(Ingredient $ingredient, float $requiredQuantity, array $context = []): array
    {
        $query = IngredientBatch::where('ingredient_id', $ingredient->id)
            ->where('quantity', '>', 0)
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                  ->orWhereDate('expiry_date', '>', now())
                  ->orWhere('allow_expired_usage', true);
            })
            ->lockForUpdate();

        match ($ingredient->batch_mode) {
            BatchMode::Fifo => $query
                ->orderByRaw('CASE WHEN received_at IS NULL THEN 1 ELSE 0 END')
                ->orderBy('received_at', 'asc')
                ->orderBy('expiry_date', 'asc')
                ->orderBy('id', 'asc'),
            default => $query  // FEFO (default & null fallback)
                ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
                ->orderBy('expiry_date', 'asc')
                ->orderBy('received_at', 'asc')
                ->orderBy('id', 'asc'),
        };

        $batches = $query->get();

        $totalAvailable = (float) $batches->sum('quantity');

        if ($totalAvailable < $requiredQuantity) {
            throw new Exception(
                "Stok tidak mencukupi untuk bahan '{$ingredient->name}'. ".
                "Dibutuhkan: {$requiredQuantity} {$ingredient->unit->value}, ".
                "Tersedia: {$totalAvailable} {$ingredient->unit->value}"
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

            StockMovement::create([
                'ingredient_id' => $ingredient->id,
                'ingredient_batch_id' => $batch->id,
                'order_id' => $context['order_id'] ?? null,
                'order_item_id' => $context['order_item_id'] ?? null,
                'stock_adjustment_id' => $context['stock_adjustment_id'] ?? null,
                'movement_type' => $context['movement_type'] ?? 'sale',
                'source_type' => $context['source_type'] ?? null,
                'source_id' => isset($context['source_id']) ? (string) $context['source_id'] : null,
                'quantity_before' => $before,
                'quantity_change' => -$deductFromThisBatch,
                'quantity_after' => $after,
                'reference' => $context['reference'] ?? null,
            ]);

            $batchChanges[] = [
                'batch_id' => $batch->id,
                'deducted' => $deductFromThisBatch,
                'remaining' => $after,
            ];
        }

        if (($context['movement_type'] ?? MovementType::Sale) === MovementType::Sale) {
            $this->recordDailyIngredientUsage(
                ingredient: $ingredient,
                usedQuantity: $requiredQuantity,
                usageDate: $context['usage_date'] ?? null,
            );
        }

        return [
            'ingredient_id' => $ingredient->id,
            'ingredient_name' => $ingredient->name,
            'total_deducted' => $requiredQuantity,
            'unit' => $ingredient->unit,
            'batch_changes' => $batchChanges,
        ];
    }

    private function recordDailyIngredientUsage(Ingredient $ingredient, float $usedQuantity, ?string $usageDate = null): void
    {
        $resolvedUsageDate = $usageDate ?: now()->toDateString();

        $dailyUsage = DailyIngredientUsage::query()
            ->where('usage_date', $resolvedUsageDate)
            ->where('ingredient_id', $ingredient->id)
            ->lockForUpdate()
            ->first();

        if ($dailyUsage) {
            $dailyUsage->fill([
                'ingredient_name' => $ingredient->name,
                'unit' => $ingredient->unit,
                'jumlah_digunakan' => round(((float) $dailyUsage->jumlah_digunakan) + $usedQuantity, 2),
            ]);
            $dailyUsage->save();

            return;
        }

        DailyIngredientUsage::create([
            'usage_date' => $resolvedUsageDate,
            'ingredient_id' => $ingredient->id,
            'ingredient_name' => $ingredient->name,
            'unit' => $ingredient->unit,
            'jumlah_digunakan' => round($usedQuantity, 2),
        ]);
    }
}
