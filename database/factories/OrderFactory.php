<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    private static int $orderSequence = 1;

    public function definition(): array
    {
        $paymentMethod = fake()->randomElement(['cash', 'qris', 'bayar_nanti']);
        $isPaid = $paymentMethod !== 'bayar_nanti';

        return [
            'order_code' => 'ORD-'.now()->format('Ymd').'-'.str_pad(self::$orderSequence++, 4, '0', STR_PAD_LEFT),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->optional(0.7)->phoneNumber(),
            'table_id' => null,
            'cashier_id' => User::factory()->state(['role' => 'cashier']),
            'status' => fake()->randomElement(['pending', 'diproses', 'selesai']),
            'order_type' => fake()->randomElement(['qr', 'cashier']),
            'payment_method' => $paymentMethod,
            'payment_proof' => null,
            'rejection_note' => null,
            'is_paid' => $isPaid,
            'total_amount' => fake()->numberBetween(10000, 200000),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'pending',
            'is_paid' => false,
            'payment_method' => 'qris',
        ]);
    }

    public function diproses(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'diproses',
            'is_paid' => true,
        ]);
    }

    public function selesai(): static
    {
        return $this->state(fn (array $attrs) => [
            'status' => 'selesai',
            'is_paid' => true,
        ]);
    }

    public function bayarNanti(): static
    {
        return $this->state(fn (array $attrs) => [
            'payment_method' => 'bayar_nanti',
            'is_paid' => false,
        ]);
    }
}
