<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromotionRuleFactory extends Factory
{
    public function definition(): array
    {
        $applicableType = fake()->randomElement(['menu', 'category']);

        return [
            'promotion_id' => Promotion::factory(),
            'applicable_type' => $applicableType,
            'applicable_id' => $applicableType === 'menu'
                ? Menu::factory()
                : Category::factory(),
        ];
    }

    public function forMenu(): static
    {
        return $this->state(fn (array $attrs) => [
            'applicable_type' => 'menu',
            'applicable_id' => Menu::factory(),
        ]);
    }

    public function forCategory(): static
    {
        return $this->state(fn (array $attrs) => [
            'applicable_type' => 'category',
            'applicable_id' => Category::factory(),
        ]);
    }
}
