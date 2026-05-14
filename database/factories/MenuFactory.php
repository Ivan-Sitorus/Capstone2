<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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

        $name = fake()->randomElement($names);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(6),
            'description' => fake()->sentence(8),
            'price' => fake()->numberBetween(8000, 50000),
            'image' => null,
            'is_available' => fake()->boolean(85),
            'is_student_discount' => fake()->boolean(30),
            'student_price' => fn (array $attrs) => $attrs['is_student_discount']
                ? fake()->numberBetween(5000, 40000)
                : null,
            'cashback' => fake()->optional(0.3)->numberBetween(1000, 5000),
            'is_stock_calculated' => fake()->boolean(20),
        ];
    }
}
