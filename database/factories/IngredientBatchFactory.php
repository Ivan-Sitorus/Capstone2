<?php

namespace Database\Factories;

use App\Models\Ingredient;
use App\Models\IngredientBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IngredientBatch>
 */
class IngredientBatchFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 0.5, 100);

        return [
            'ingredient_id' => Ingredient::factory(),
            'quantity' => $quantity,
            'expiry_date' => fake()->optional(0.7)->dateTimeBetween('+1 week', '+6 months'),
            'received_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'cost_per_unit' => fake()->randomFloat(2, 100, 50000),
        ];
    }
}
