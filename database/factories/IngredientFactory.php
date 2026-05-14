<?php

namespace Database\Factories;

use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingredient>
 */
class IngredientFactory extends Factory
{
    public function definition(): array
    {
        $names = [
            'Kopi Robusta', 'Kopi Arabika', 'Susu Cair', 'Susu Kental Manis',
            'Gula Pasir', 'Gula Merah', 'Teh Celup', 'Matcha Bubuk',
            'Coklat Bubuk', 'Tepung Terigu', 'Telur', 'Mentega',
            'Nasi', 'Mie', 'Minyak Goreng', 'Garam',
            'Keju', 'Selai Stroberi', 'Sirup Vanilla', 'Es Batu',
        ];

        return [
            'name' => fake()->randomElement($names),
            'unit' => fake()->randomElement(['gram', 'kg', 'ml', 'liter', 'pcs', 'sachet', 'sdm', 'sdt']),
            'low_stock_threshold' => fake()->randomFloat(2, 0, 50),
            'is_active' => fake()->boolean(90),
        ];
    }
}
