<?php

namespace Database\Factories;

use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuIngredient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuIngredient>
 */
class MenuIngredientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'ingredient_id' => Ingredient::factory(),
            'quantity_used' => fake()->randomFloat(2, 0.5, 50),
        ];
    }
}
