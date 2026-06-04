<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\MenuStock;
use App\Models\MenuStockBatch;
use App\Models\User;
use App\Services\MenuStockReconciliationService;
use App\Services\StockReconciliationService;
use Illuminate\Database\Seeder;

class StockAdjustmentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        if (! $admin) {
            $this->command->error('Tidak ada admin. Jalankan UserSeeder dulu.');

            return;
        }

        $stockService = app(StockReconciliationService::class);
        $menuStockService = app(MenuStockReconciliationService::class);

        // ── Stock Adjustment Bahan Baku ─────────────────────────────────
        $ingredients = Ingredient::take(3)->get();

        foreach ($ingredients as $i => $ing) {
            $stockService->createManualAdjustment(
                ingredientId: $ing->id,
                quantity: ($i + 1) * 5,
                adjustmentType: 'increase',
                reason: 'Adjustment awal stok bahan baku',
                reportedBy: $admin->id,
            );
        }

        // ── Stock Adjustment Stok Menu ──────────────────────────────────
        $menuStock = MenuStock::first();

        if ($menuStock) {
            // Buat batch dulu
            $batch = MenuStockBatch::create([
                'menu_stock_id' => $menuStock->id,
                'quantity' => 50,
                'expiry_date' => now()->addMonths(6),
                'received_at' => now(),
            ]);

            $menuStockService->createManualAdjustment(
                menuStockId: $menuStock->id,
                quantity: 10,
                adjustmentType: 'increase',
                reason: 'Adjustment awal stok menu',
                reportedBy: $admin->id,
            );
        }

        $this->command->info(
            count($ingredients).' stock adjustments & '.
            ($menuStock ? '1 menu stock adjustment' : '0 menu stock adjustment').
            ' berhasil dibuat.'
        );
    }
}
