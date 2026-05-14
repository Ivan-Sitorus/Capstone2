<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppliedPromotionFactory extends Factory
{
    public function definition(): array
    {
        $discountType = fake()->randomElement(['percentage', 'fixed_amount', 'buy_x_get_y', 'bundle']);
        $discountValue = $discountType === 'percentage'
            ? fake()->randomElement([10, 15, 20, 25])
            : fake()->numberBetween(2000, 30000);

        return [
            'order_id' => Order::factory(),
            'promotion_id' => fake()->optional(0.8)->factory(Promotion::factory()),
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_amount' => fake()->numberBetween(1000, 30000),
        ];
    }

    public function percentage(): static
    {
        return $this->state(fn (array $attrs) => [
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'discount_amount' => fn (array $attrs) => (int) round(($attrs['discount_value'] ?? 10) / 100 * 50000),
        ]);
    }
}
