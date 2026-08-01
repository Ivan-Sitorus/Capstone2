<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\User;
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

        $ingredients = Ingredient::take(3)->get();
        if ($ingredients->isEmpty()) {
            $this->command->error('Tidak ada bahan baku. Jalankan MenuSeeder dulu.');
            return;
        }

        $count = 0;
        foreach ($ingredients as $i => $ing) {
            // Pastikan ada batch stok untuk ingredient ini
            $batch = IngredientBatch::where('ingredient_id', $ing->id)->first();
            if (! $batch) {
                $batch = IngredientBatch::create([
                    'ingredient_id' => $ing->id,
                    'quantity' => 100,
                    'initial_quantity' => 100,
                    'cost_per_unit' => 5000,
                    'total_cost' => 100 * 5000,
                    'supplier_name' => 'CV Tani Makmur',
                    'payment_status' => 'lunas',
                    'received_at' => now(),
                    'expiry_date' => now()->addMonths(6),
                ]);
            }

            // Buat adjustment increase
            $adjustment = StockAdjustment::create([
                'ingredient_id' => $ing->id,
                'adjustment_type' => StockAdjustment::TYPE_INCREASE,
                'category' => array_key_first(StockAdjustment::INCREASE_CATEGORIES),
                'quantity' => ($i + 1) * 10,
                'quantity_before' => $batch->quantity,
                'quantity_after' => $batch->quantity + (($i + 1) * 10),
                'reason' => 'Adjustment awal stok untuk testing',
                'reported_by' => $admin->id,
                'adjusted_at' => now(),
                'status' => StockAdjustment::STATUS_ACTIVE,
            ]);

            // Update batch quantity
            $batch->increment('quantity', ($i + 1) * 10);

            // Catat stock movement
            StockMovement::create([
                'ingredient_id' => $ing->id,
                'ingredient_batch_id' => $batch->id,
                'stock_adjustment_id' => $adjustment->id,
                'movement_type' => 'adjustment_increase',
                'source_type' => 'stock_adjustment',
                'source_id' => (string) $adjustment->id,
                'quantity_before' => $batch->quantity - (($i + 1) * 10),
                'quantity_change' => ($i + 1) * 10,
                'quantity_after' => $batch->quantity,
                'reference' => 'ADJ-' . str_pad($adjustment->id, 3, '0', STR_PAD_LEFT),
                'notes' => 'Adjustment awal stok untuk testing',
                'recorded_by' => $admin->id,
            ]);

            $count++;
        }

        $this->command->info("{$count} stock adjustments berhasil dibuat.");
    }
}
