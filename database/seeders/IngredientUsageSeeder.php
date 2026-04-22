<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Seeder data pemakaian bahan baku harian — 5 bulan (2025-11-01 s/d 2026-03-31).
 *
 * 15 bahan baku × 151 hari = 2265 baris daily_ingredient_usages.
 * Pola pemakaian bervariasi per kategori bahan baku untuk keperluan:
 *   - K-Means Clustering (klasterisasi level penggunaan)
 *   - Prophet Time Series (prediksi kebutuhan harian)
 *
 * Kategori & pola:
 *   Bahan Minuman   : Bubuk Kopi, Susu Cair, Sirup Gula, Teh Celup, Krimer
 *   Bahan Makanan   : Tepung Terigu, Telur Ayam, Minyak Goreng, Bawang Merah, Garam
 *   Bahan Dessert   : Gula Pasir, Coklat Bubuk, Mentega, Keju Parut, Sirup Vanila
 */
class IngredientUsageSeeder extends Seeder
{
    public function run(): void
    {
        // ── Hapus data lama ────────────────────────────────────────────
        DB::table('daily_ingredient_usages')->delete();
        DB::table('ingredients')->whereIn('name', $this->ingredientNames())->delete();

        // ── Definisi bahan baku ────────────────────────────────────────
        $ingredients = [
            // Bahan Minuman
            ['name' => 'Bubuk Kopi',    'unit' => 'gram', 'low_stock_threshold' => 500],
            ['name' => 'Susu Cair',     'unit' => 'ml',   'low_stock_threshold' => 2000],
            ['name' => 'Sirup Gula',    'unit' => 'ml',   'low_stock_threshold' => 500],
            ['name' => 'Teh Celup',     'unit' => 'pcs',  'low_stock_threshold' => 50],
            ['name' => 'Krimer',        'unit' => 'gram', 'low_stock_threshold' => 300],
            // Bahan Makanan
            ['name' => 'Tepung Terigu', 'unit' => 'gram', 'low_stock_threshold' => 500],
            ['name' => 'Telur Ayam',    'unit' => 'pcs',  'low_stock_threshold' => 30],
            ['name' => 'Minyak Goreng', 'unit' => 'ml',   'low_stock_threshold' => 1000],
            ['name' => 'Bawang Merah',  'unit' => 'gram', 'low_stock_threshold' => 200],
            ['name' => 'Garam',         'unit' => 'gram', 'low_stock_threshold' => 100],
            // Bahan Dessert / Topping
            ['name' => 'Gula Pasir',    'unit' => 'gram', 'low_stock_threshold' => 1000],
            ['name' => 'Coklat Bubuk',  'unit' => 'gram', 'low_stock_threshold' => 300],
            ['name' => 'Mentega',       'unit' => 'gram', 'low_stock_threshold' => 200],
            ['name' => 'Keju Parut',    'unit' => 'gram', 'low_stock_threshold' => 150],
            ['name' => 'Sirup Vanila',  'unit' => 'ml',   'low_stock_threshold' => 200],
        ];

        $now = now()->toDateTimeString();
        foreach ($ingredients as &$ing) {
            $ing['is_active']  = true;
            $ing['created_at'] = $now;
            $ing['updated_at'] = $now;
        }
        unset($ing);

        DB::table('ingredients')->insert($ingredients);

        $ingIds = DB::table('ingredients')
            ->whereIn('name', array_column($ingredients, 'name'))
            ->pluck('id', 'name');

        // ── Pola pemakaian harian (base weekday, noise ±, trend/bulan) ─
        // Setiap bahan memiliki base berbeda + sedikit tren naik/turun
        // untuk menghasilkan time series yang bervariasi dan bermakna.
        $profile = [
            // nama              base  noise  trend(per hari)  weekend_boost
            'Bubuk Kopi'    => ['base' => 310, 'noise' => 55, 'trend' =>  0.15, 'wknd' => 1.25],
            'Susu Cair'     => ['base' => 270, 'noise' => 50, 'trend' =>  0.10, 'wknd' => 1.20],
            'Sirup Gula'    => ['base' => 180, 'noise' => 35, 'trend' =>  0.05, 'wknd' => 1.15],
            'Teh Celup'     => ['base' => 155, 'noise' => 30, 'trend' =>  0.08, 'wknd' => 1.20],
            'Krimer'        => ['base' => 130, 'noise' => 28, 'trend' =>  0.06, 'wknd' => 1.15],
            'Tepung Terigu' => ['base' => 105, 'noise' => 22, 'trend' =>  0.04, 'wknd' => 1.30],
            'Telur Ayam'    => ['base' =>  88, 'noise' => 18, 'trend' =>  0.03, 'wknd' => 1.25],
            'Minyak Goreng' => ['base' =>  72, 'noise' => 15, 'trend' => -0.02, 'wknd' => 1.20],
            'Bawang Merah'  => ['base' =>  58, 'noise' => 14, 'trend' =>  0.02, 'wknd' => 1.10],
            'Garam'         => ['base' =>  45, 'noise' => 10, 'trend' =>  0.00, 'wknd' => 1.05],
            'Gula Pasir'    => ['base' => 200, 'noise' => 40, 'trend' =>  0.12, 'wknd' => 1.20],
            'Coklat Bubuk'  => ['base' =>  95, 'noise' => 20, 'trend' =>  0.05, 'wknd' => 1.25],
            'Mentega'       => ['base' =>  78, 'noise' => 16, 'trend' =>  0.03, 'wknd' => 1.20],
            'Keju Parut'    => ['base' =>  48, 'noise' => 12, 'trend' =>  0.02, 'wknd' => 1.15],
            'Sirup Vanila'  => ['base' =>  38, 'noise' =>  9, 'trend' =>  0.03, 'wknd' => 1.15],
        ];

        $startDate = Carbon::create(2025, 11, 1);
        $endDate   = Carbon::create(2026,  3, 31);

        $rows    = [];
        $dayIdx  = 0;
        $current = $startDate->copy();
        mt_srand(42);

        while ($current->lte($endDate)) {
            $isWeekend = $current->isWeekend();

            foreach ($ingredients as $ing) {
                $name = $ing['name'];
                $p    = $profile[$name];

                $trendOffset = $dayIdx * $p['trend'];
                $base        = $p['base'] + $trendOffset;
                $noise       = mt_rand(-(int)$p['noise'], (int)$p['noise']);
                $factor      = $isWeekend ? $p['wknd'] : 1.0;
                $jumlah      = max(0, round(($base + $noise) * $factor, 2));

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

            $dayIdx++;
            $current->addDay();
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('daily_ingredient_usages')->insert($chunk);
        }

        $this->command->info(
            'IngredientUsageSeeder: ' . count($ingIds) . ' bahan baku, ' .
            count($rows) . ' baris daily_ingredient_usages ' .
            '(2025-11-01 s/d 2026-03-31, 5 bulan).'
        );
    }

    private function ingredientNames(): array
    {
        return [
            'Bubuk Kopi', 'Susu Cair', 'Sirup Gula', 'Teh Celup', 'Krimer',
            'Tepung Terigu', 'Telur Ayam', 'Minyak Goreng', 'Bawang Merah', 'Garam',
            'Gula Pasir', 'Coklat Bubuk', 'Mentega', 'Keju Parut', 'Sirup Vanila',
        ];
    }
}
