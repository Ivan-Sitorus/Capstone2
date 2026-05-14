<?php

namespace Database\Factories;

use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockAdjustmentFactory extends Factory
{
    public function definition(): array
    {
        $adjustmentType = fake()->randomElement(['increase', 'decrease']);
        $quantity = fake()->randomFloat(2, 0.5, 50);
        $quantityBefore = fake()->randomFloat(2, 10, 200);

        return [
            'ingredient_id' => Ingredient::factory(),
            'adjustment_type' => $adjustmentType,
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $adjustmentType === 'increase'
                ? $quantityBefore + $quantity
                : max(0, $quantityBefore - $quantity),
            'reason' => fake()->randomElement([
                'Stok opname', 'Barang rusak', 'Kadaluarsa', 'Kesalahan pencatatan',
                'Pembelian baru', 'Dikembalikan ke supplier', 'Koreksi stok',
            ]),
            'reported_by' => User::factory()->state(['role' => 'cashier']),
            'adjusted_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
