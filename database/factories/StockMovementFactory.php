<?php

namespace Database\Factories;

use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    public function definition(): array
    {
        $movementType = fake()->randomElement(['purchase', 'sale', 'waste', 'adjustment_increase', 'adjustment_decrease', 'correction']);
        $quantityChange = fake()->randomFloat(2, 0.5, 30);
        $quantityBefore = fake()->randomFloat(2, 10, 200);

        return [
            'ingredient_id' => Ingredient::factory(),
            'ingredient_batch_id' => null,
            'order_id' => null,
            'order_item_id' => null,
            'stock_adjustment_id' => null,
            'movement_type' => $movementType,
            'source_type' => null,
            'source_id' => null,
            'quantity_before' => $quantityBefore,
            'quantity_change' => $quantityChange,
            'quantity_after' => in_array($movementType, ['purchase', 'adjustment_increase', 'correction'])
                ? $quantityBefore + $quantityChange
                : max(0, $quantityBefore - $quantityChange),
            'unit_cost' => fake()->optional(0.7)->randomFloat(2, 100, 25000),
            'reference' => fake()->optional(0.5)->bothify('PO-####-??'),
            'notes' => fake()->optional(0.4)->sentence(),
            'recorded_by' => null,
        ];
    }
}
