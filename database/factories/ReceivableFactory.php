<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReceivableFactory extends Factory
{
    public function definition(): array
    {
        $totalAmount = fake()->numberBetween(25000, 500000);
        $status = fake()->randomElement(['pending', 'partial', 'paid', 'overdue']);

        return [
            'customer_name' => fake()->name(),
            'invoice_date' => fake()->dateTimeBetween('-2 months', 'now'),
            'amount' => $totalAmount,
            'due_date' => fake()->optional(0.8)->dateTimeBetween('now', '+3 months'),
            'status' => $status,
            'paid_amount' => match ($status) {
                'paid' => $totalAmount,
                'partial' => (int) ($totalAmount * fake()->randomFloat(2, 0.1, 0.9)),
                default => 0,
            },
            'notes' => fake()->optional(0.3)->sentence(),
            'order_id' => Order::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'pending',
            'paid_amount' => 0,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'paid',
            'paid_amount' => $attrs['amount'],
        ]);
    }
}
