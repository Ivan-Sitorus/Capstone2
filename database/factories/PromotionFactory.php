<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PromotionFactory extends Factory
{
    public function definition(): array
    {
        $promotionType = fake()->randomElement(['percentage', 'fixed_amount', 'buy_x_get_y', 'bundle']);
        $discountValue = $promotionType === 'percentage'
            ? fake()->randomElement([10, 15, 20, 25, 30, 50])
            : fake()->numberBetween(2000, 50000);

        return [
            'name' => fake()->randomElement([
                'Diskon Kopi Pagi', 'Promo Akhir Pekan', 'Diskon Mahasiswa',
                'Beli 1 Gratis 1', 'Paket Hemat', 'Diskon Spesial',
                'Promo Ramadhan', 'Diskon Ulang Tahun', 'Paket Berdua',
                'Promo Hari Besar',
            ]),
            'type' => $promotionType,
            'discount_value' => $discountValue,
            'min_purchase' => fake()->optional(0.5)->numberBetween(20000, 100000),
            'start_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'end_date' => fake()->dateTimeBetween('+1 week', '+3 months'),
            'status' => fake()->randomElement(['active', 'inactive', 'scheduled', 'expired']),
            'applicable_items' => null,
            'description' => fake()->optional(0.6)->sentence(),
            'usage_limit' => fake()->optional(0.4)->numberBetween(10, 500),
            'usage_count' => fake()->numberBetween(0, 50),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'active',
            'start_date' => now()->subDays(5),
            'end_date' => now()->addDays(25),
        ]);
    }

    public function percentage(): static
    {
        return $this->state(fn (array $attrs) => [
            'type' => 'percentage',
            'discount_value' => fake()->randomElement([10, 15, 20, 25]),
        ]);
    }
}
