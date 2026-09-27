<?php

namespace Database\Factories;

use App\Enums\MenuStatus;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    public function definition(): array
    {
        $names = [
            'Kopi Robusta', 'Kopi Arabika', 'Kopi Latte', 'Kopi Cappuccino',
            'Teh Manis', 'Teh Tarik', 'Matcha Latte', 'Coklat Panas',
            'Roti Bakar', 'Pisang Goreng', 'Kentang Goreng', 'Nasi Goreng',
            'Mie Goreng', 'Ayam Penyet', 'Es Jeruk', 'Jus Alpukat',
            'Jus Mangga', 'Susu Coklat', 'Croissant', 'Brownies',
        ];

        $price = fake()->numberBetween(8000, 50000);

        return [
            'category_id' => Category::factory(),
            'name' => fake()->randomElement($names),
            'price' => $price,
            'cost_price' => (int) floor($price * fake()->randomFloat(2, 0.3, 0.7)),
            'discounted_price' => null,
            'image' => null,
            'status' => MenuStatus::Active->value,
        ];
    }
}
