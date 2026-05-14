<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UnexpectedTransactionFactory extends Factory
{
    public function definition(): array
    {
        $jenis = fake()->randomElement(['pemasukan', 'pengeluaran']);

        return [
            'jenis' => $jenis,
            'nominal' => fake()->randomFloat(2, 1000, 5000000),
            'deskripsi' => $jenis === 'pemasukan'
                ? fake()->randomElement([
                    'Donasi', 'Tips dari pelanggan', 'Penjualan barang bekas',
                    'Pengembalian dana', 'Bonus dari supplier',
                ])
                : fake()->randomElement([
                    'Perbaikan AC', 'Beli alat kebersihan', 'Ganti lampu',
                    'Biaya parkir', 'Donasi acara', 'Biaya kirim barang',
                    'Kebocoran pipa', 'Service mesin kopi',
                ]),
        ];
    }
}
