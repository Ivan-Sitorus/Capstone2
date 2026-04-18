<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Seeder data pemakaian bahan baku harian untuk keperluan Data Mining (K-Means Clustering).
 *
 * 10 bahan baku × 243 hari (2025-08-01 s/d 2026-03-31) = 2430 baris daily_ingredient_usages.
 * Setiap bahan baku memiliki pola pemakaian berbeda sehingga K-Means dapat membentuk
 * klaster yang bermakna (Sangat Banyak / Banyak / Cukup / Sedikit / Paling Sedikit).
 */
class IngredientUsageSeeder extends Seeder
{
    public function run(): void
    {
        // ── Hapus data lama ────────────────────────────────────────────
        DB::table('daily_ingredient_usages')->delete();
        DB::table('ingredients')->whereIn('name', $this->ingredientNames())->delete();

        // ── Insert ingredients ─────────────────────────────────────────
        $ingredients = [
            ['name' => 'Bubuk Kopi',     'unit' => 'gram',   'low_stock_threshold' => 500,  'is_active' => true],
            ['name' => 'Susu Cair',      'unit' => 'ml',     'low_stock_threshold' => 2000, 'is_active' => true],
            ['name' => 'Gula Pasir',     'unit' => 'gram',   'low_stock_threshold' => 1000, 'is_active' => true],
            ['name' => 'Teh Celup',      'unit' => 'pcs',    'low_stock_threshold' => 50,   'is_active' => true],
            ['name' => 'Coklat Bubuk',   'unit' => 'gram',   'low_stock_threshold' => 300,  'is_active' => true],
            ['name' => 'Mentega',        'unit' => 'gram',   'low_stock_threshold' => 200,  'is_active' => true],
            ['name' => 'Keju Parut',     'unit' => 'gram',   'low_stock_threshold' => 150,  'is_active' => true],
            ['name' => 'Sirup Vanila',   'unit' => 'ml',     'low_stock_threshold' => 200,  'is_active' => true],
            ['name' => 'Telur Ayam',     'unit' => 'pcs',    'low_stock_threshold' => 30,   'is_active' => true],
            ['name' => 'Tepung Terigu',  'unit' => 'gram',   'low_stock_threshold' => 500,  'is_active' => true],
        ];

        $now = now()->toDateTimeString();
        foreach ($ingredients as &$ing) {
            $ing['created_at'] = $now;
            $ing['updated_at'] = $now;
        }
        unset($ing);

        DB::table('ingredients')->insert($ingredients);

        $ingIds = DB::table('ingredients')
            ->whereIn('name', array_column($ingredients, 'name'))
            ->pluck('id', 'name');

        // ── Basis pemakaian harian per bahan baku ─────────────────────
        // Nilai dirancang agar terbentuk klaster yang terbedakan:
        //   Bubuk Kopi, Susu Cair   → Sangat Banyak (volume tinggi)
        //   Gula Pasir, Teh Celup   → Banyak
        //   Coklat Bubuk, Mentega   → Cukup
        //   Keju Parut, Sirup Vanila → Sedikit
        //   Telur Ayam, Tepung Terigu → Paling Sedikit (jarang dipakai)
        $baseUsage = [
            'Bubuk Kopi'    => ['base' => 320, 'noise' => 60],
            'Susu Cair'     => ['base' => 280, 'noise' => 55],
            'Gula Pasir'    => ['base' => 190, 'noise' => 40],
            'Teh Celup'     => ['base' => 170, 'noise' => 35],
            'Coklat Bubuk'  => ['base' => 110, 'noise' => 25],
            'Mentega'       => ['base' =>  90, 'noise' => 20],
            'Keju Parut'    => ['base' =>  55, 'noise' => 15],
            'Sirup Vanila'  => ['base' =>  45, 'noise' => 12],
            'Telur Ayam'    => ['base' =>  20, 'noise' =>  8],
            'Tepung Terigu' => ['base' =>  15, 'noise' =>  6],
        ];

        $startDate = Carbon::create(2025, 8, 1);
        $endDate   = Carbon::create(2026, 3, 31);

        $rows = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $isWeekend = $current->isWeekend();

            foreach ($ingredients as $ing) {
                $name    = $ing['name'];
                $config  = $baseUsage[$name];
                $base    = $config['base'];
                $noise   = $config['noise'];

                // Weekend boost ~20 %
                $factor  = $isWeekend ? 1.20 : 1.0;
                $jumlah  = round(($base + mt_rand(-$noise, $noise)) * $factor, 2);
                $jumlah  = max(0, $jumlah);

                $rows[] = [
                    'usage_date'       => $current->toDateString(),
                    'ingredient_id'    => $ingIds[$name],
                    'ingredient_name'  => $name,
                    'unit'             => $ing['unit'],
                    'jumlah_digunakan' => $jumlah,
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }

            $current->addDay();
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('daily_ingredient_usages')->insert($chunk);
        }

        $this->command->info(
            'IngredientUsageSeeder: ' . count($ingIds) . ' bahan baku, ' .
            count($rows) . ' baris daily_ingredient_usages ' .
            '(2025-08-01 s/d 2026-03-31).'
        );
    }

    private function ingredientNames(): array
    {
        return [
            'Bubuk Kopi', 'Susu Cair', 'Gula Pasir', 'Teh Celup',
            'Coklat Bubuk', 'Mentega', 'Keju Parut', 'Sirup Vanila',
            'Telur Ayam', 'Tepung Terigu',
        ];
    }
}
