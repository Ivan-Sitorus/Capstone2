<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\IngredientBatch;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CashFlowIngredientSeeder extends Seeder
{
    public function run(): void
    {
        // ── Bahan baku utama cafe ─────────────────────────────────────
        $ingredients = [
            ['name' => 'Biji Kopi Robusta',   'unit' => 'kg',     'threshold' => 2],
            ['name' => 'Biji Kopi Arabica',    'unit' => 'kg',     'threshold' => 1],
            ['name' => 'Susu Full Cream',      'unit' => 'liter',  'threshold' => 5],
            ['name' => 'Gula Pasir',           'unit' => 'kg',     'threshold' => 3],
            ['name' => 'Teh Hitam',            'unit' => 'gram',   'threshold' => 200],
            ['name' => 'Cokelat Bubuk',        'unit' => 'gram',   'threshold' => 300],
            ['name' => 'Krimer Bubuk',         'unit' => 'gram',   'threshold' => 500],
            ['name' => 'Sirup Vanilla',        'unit' => 'ml',     'threshold' => 200],
            ['name' => 'Sirup Caramel',        'unit' => 'ml',     'threshold' => 200],
            ['name' => 'Roti Tawar',           'unit' => 'pcs',    'threshold' => 10],
            ['name' => 'Mentega',              'unit' => 'gram',   'threshold' => 200],
            ['name' => 'Telur Ayam',           'unit' => 'pcs',    'threshold' => 20],
            ['name' => 'Es Batu',              'unit' => 'kg',     'threshold' => 5],
            ['name' => 'Air Mineral',          'unit' => 'liter',  'threshold' => 10],
            ['name' => 'Kental Manis',         'unit' => 'sachet', 'threshold' => 20],
        ];

        $saved = [];
        foreach ($ingredients as $ing) {
            $saved[$ing['name']] = Ingredient::firstOrCreate(
                ['name' => $ing['name']],
                ['unit' => $ing['unit'], 'low_stock_threshold' => $ing['threshold'], 'is_active' => true]
            );
        }

        // ── Pembelian bahan baku (bulan ini & beberapa bulan lalu) ────
        $batches = [

            // ── Januari ──────────────────────────────────────────────
            ['name' => 'Biji Kopi Robusta',  'qty' => 5,    'cpu' => 85000,  'date' => '2026-01-05'],
            ['name' => 'Susu Full Cream',    'qty' => 12,   'cpu' => 18500,  'date' => '2026-01-05'],
            ['name' => 'Gula Pasir',         'qty' => 10,   'cpu' => 14000,  'date' => '2026-01-08'],
            ['name' => 'Teh Hitam',          'qty' => 500,  'cpu' => 120,    'date' => '2026-01-10'],
            ['name' => 'Cokelat Bubuk',      'qty' => 800,  'cpu' => 95,     'date' => '2026-01-12'],
            ['name' => 'Roti Tawar',         'qty' => 30,   'cpu' => 8500,   'date' => '2026-01-15'],
            ['name' => 'Kental Manis',       'qty' => 48,   'cpu' => 4500,   'date' => '2026-01-18'],
            ['name' => 'Sirup Vanilla',      'qty' => 600,  'cpu' => 85,     'date' => '2026-01-20'],
            ['name' => 'Es Batu',            'qty' => 20,   'cpu' => 5000,   'date' => '2026-01-22'],
            ['name' => 'Biji Kopi Arabica',  'qty' => 3,    'cpu' => 145000, 'date' => '2026-01-25'],

            // ── Februari ─────────────────────────────────────────────
            ['name' => 'Biji Kopi Robusta',  'qty' => 5,    'cpu' => 87000,  'date' => '2026-02-03'],
            ['name' => 'Susu Full Cream',    'qty' => 15,   'cpu' => 18500,  'date' => '2026-02-03'],
            ['name' => 'Gula Pasir',         'qty' => 8,    'cpu' => 14500,  'date' => '2026-02-07'],
            ['name' => 'Mentega',            'qty' => 500,  'cpu' => 48,     'date' => '2026-02-10'],
            ['name' => 'Telur Ayam',         'qty' => 60,   'cpu' => 2500,   'date' => '2026-02-10'],
            ['name' => 'Cokelat Bubuk',      'qty' => 1000, 'cpu' => 95,     'date' => '2026-02-14'],
            ['name' => 'Sirup Caramel',      'qty' => 600,  'cpu' => 90,     'date' => '2026-02-17'],
            ['name' => 'Krimer Bubuk',       'qty' => 1000, 'cpu' => 55,     'date' => '2026-02-20'],
            ['name' => 'Es Batu',            'qty' => 25,   'cpu' => 5000,   'date' => '2026-02-24'],
            ['name' => 'Roti Tawar',         'qty' => 40,   'cpu' => 8500,   'date' => '2026-02-26'],

            // ── Maret ────────────────────────────────────────────────
            ['name' => 'Biji Kopi Robusta',  'qty' => 6,    'cpu' => 87000,  'date' => '2026-03-02'],
            ['name' => 'Biji Kopi Arabica',  'qty' => 2,    'cpu' => 148000, 'date' => '2026-03-02'],
            ['name' => 'Susu Full Cream',    'qty' => 18,   'cpu' => 19000,  'date' => '2026-03-05'],
            ['name' => 'Gula Pasir',         'qty' => 12,   'cpu' => 14500,  'date' => '2026-03-08'],
            ['name' => 'Teh Hitam',          'qty' => 600,  'cpu' => 120,    'date' => '2026-03-10'],
            ['name' => 'Kental Manis',       'qty' => 60,   'cpu' => 4500,   'date' => '2026-03-12'],
            ['name' => 'Sirup Vanilla',      'qty' => 500,  'cpu' => 85,     'date' => '2026-03-15'],
            ['name' => 'Air Mineral',        'qty' => 30,   'cpu' => 3500,   'date' => '2026-03-18'],
            ['name' => 'Mentega',            'qty' => 600,  'cpu' => 48,     'date' => '2026-03-20'],
            ['name' => 'Es Batu',            'qty' => 30,   'cpu' => 5000,   'date' => '2026-03-25'],

            // ── April (bulan ini) ─────────────────────────────────────
            ['name' => 'Biji Kopi Robusta',  'qty' => 7,    'cpu' => 88000,  'date' => '2026-04-01'],
            ['name' => 'Susu Full Cream',    'qty' => 20,   'cpu' => 19000,  'date' => '2026-04-01'],
            ['name' => 'Gula Pasir',         'qty' => 10,   'cpu' => 15000,  'date' => '2026-04-03'],
            ['name' => 'Telur Ayam',         'qty' => 60,   'cpu' => 2600,   'date' => '2026-04-04'],
            ['name' => 'Roti Tawar',         'qty' => 50,   'cpu' => 9000,   'date' => '2026-04-06'],
            ['name' => 'Cokelat Bubuk',      'qty' => 1000, 'cpu' => 98,     'date' => '2026-04-08'],
            ['name' => 'Sirup Caramel',      'qty' => 500,  'cpu' => 90,     'date' => '2026-04-10'],
            ['name' => 'Krimer Bubuk',       'qty' => 1200, 'cpu' => 55,     'date' => '2026-04-11'],
            ['name' => 'Kental Manis',       'qty' => 48,   'cpu' => 4800,   'date' => '2026-04-12'],
            ['name' => 'Biji Kopi Arabica',  'qty' => 3,    'cpu' => 150000, 'date' => '2026-04-14'],
            ['name' => 'Teh Hitam',          'qty' => 500,  'cpu' => 125,    'date' => '2026-04-15'],
            ['name' => 'Es Batu',            'qty' => 25,   'cpu' => 5500,   'date' => '2026-04-16'],
            ['name' => 'Sirup Vanilla',      'qty' => 400,  'cpu' => 88,     'date' => '2026-04-17'],
            ['name' => 'Air Mineral',        'qty' => 24,   'cpu' => 3500,   'date' => '2026-04-18'],
            ['name' => 'Mentega',            'qty' => 400,  'cpu' => 50,     'date' => '2026-04-19'],
        ];

        foreach ($batches as $b) {
            if (!isset($saved[$b['name']])) continue;

            IngredientBatch::create([
                'ingredient_id' => $saved[$b['name']]->id,
                'quantity'      => $b['qty'],
                'cost_per_unit' => $b['cpu'],
                'received_at'   => Carbon::parse($b['date'])->setHour(8)->setMinute(0),
                'expiry_date'   => Carbon::parse($b['date'])->addDays(rand(30, 180)),
            ]);
        }

        $this->command->info('✓ ' . count($batches) . ' batch bahan baku berhasil di-seed.');
    }
}
