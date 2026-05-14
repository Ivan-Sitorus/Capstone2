<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_method' => fake()->randomElement(['qris', 'ewallet', 'cash', 'transfer']),
            'payment_gateway' => 'manual',
            'transaction_id' => fake()->unique()->bothify('TXN-########'),
            'amount' => fake()->numberBetween(10000, 200000),
            'status' => fake()->randomElement(['pending', 'success', 'failed']),
            'paid_at' => fake()->optional(0.7)->dateTimeBetween('-1 week', 'now'),
        ];
    }

    public function success(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'success',
            'paid_at' => now(),
        ]);
    }
}
