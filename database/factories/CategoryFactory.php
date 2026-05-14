<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Kopi', 'Teh', 'Coklat', 'Snack', 'Makanan Berat',
            'Minuman Segar', 'Jus', 'Susu', 'Pastry', 'Nasi',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'is_active' => fake()->boolean(90),
        ];
    }
}
