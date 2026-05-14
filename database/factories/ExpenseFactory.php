<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vendor' => fake()->randomElement([
                'PT Kopi Nusantara', 'UD Susu Segar', 'Toko Bahan Kue',
                'Indomaret', 'Alfamart', 'Pasar Tradisional',
                'Air Minum Cahaya', 'PT Gas Indo', 'PDAM',
                'PLN', 'Telkom Indonesia', 'Kopkar W9',
            ]),
            'category' => fake()->randomElement([
                'Bahan Baku', 'Minuman', 'Makanan',
                'Utilitas', 'Operasional', 'Kebersihan',
                'ATK', 'Perawatan', 'Transportasi', 'Lainnya',
            ]),
            'amount' => fake()->numberBetween(5000, 2000000),
            'date' => fake()->dateTimeBetween('-3 months', 'now'),
            'description' => fake()->optional(0.7)->sentence(),
            'payment_method' => fake()->randomElement(['cash', 'transfer', 'qris']),
        ];
    }
}
