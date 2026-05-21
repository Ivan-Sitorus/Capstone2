<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HistoricalDataSeeder extends Seeder
{
    /**
     * menu_id => [harga, qty_weekday, qty_weekend]
     * qty_weekend = 0 → menu jarang terjual weekend (20% kemungkinan muncul)
     * Semua menu DIJAMIN terjual setiap weekday (min 1 unit/hari).
     */
    private array $menus = [
        4  => [14000, 3, 2],  // Kopi Susu
        3  => [12000, 2, 1],  // Es Americano
        2  => [10000, 2, 1],  // Americano Panas
        1  => [10000, 2, 1],  // Espresso
        6  => [4000,  2, 1],  // Teh Manis
        7  => [7000,  1, 1],  // Teh Susu
        5  => [3000,  1, 0],  // Teh Tawar (jarang weekend)
        9  => [6000,  1, 1],  // Teh Jeruk
        8  => [5000,  1, 0],  // Jeruk Nipis (jarang weekend)
        10 => [8000,  2, 1],  // Full Chocolate
        11 => [8000,  1, 0],  // Matcha (jarang weekend)
        12 => [8000,  1, 1],  // Vanilla Latte
        13 => [8000,  1, 0],  // Creamy Chocolatey (jarang weekend)
        19 => [12000, 2, 1],  // Nasgor Telur
        20 => [17000, 1, 1],  // Nasgor Ayam/Udang
        17 => [10000, 2, 1],  // Mie Goreng Telur
        18 => [10000, 1, 0],  // Mie Rebus Telur (jarang weekend)
        21 => [9000,  2, 1],  // Nasi Telur Saus
        22 => [8000,  1, 1],  // Nasi Telur Kecap
        23 => [14000, 1, 1],  // Nasi Ayam Geprek
        14 => [10000, 1, 0],  // Pisang Coklat Keju (jarang weekend)
        15 => [8000,  1, 0],  // Tempe Mendoan (jarang weekend)
        16 => [12000, 1, 1],  // Kentang (French Fries)
        30 => [20000, 1, 1],  // French Fries
        24 => [27000, 1, 0],  // Cheesecake (jarang weekend)
    ];

    // id => [name, unit, base_daily_weekday]  — weekend = base * 0.75
    private array $ingredients = [
        12 => ['Bubuk Kopi',        'gram',   100],
        13 => ['Susu Cair',         'ml',     1000],
        14 => ['Sirup Gula',        'ml',     200],
        15 => ['Teh Celup',         'pcs',    20],
        16 => ['Krimer',            'gram',   100],
        17 => ['Tepung Terigu',     'gram',   200],
        18 => ['Telur Ayam',        'pcs',    10],
        19 => ['Minyak Goreng',     'ml',     200],
        20 => ['Bawang Merah',      'gram',   100],
        21 => ['Garam',             'gram',   35],
        22 => ['Gula Pasir',        'gram',   125],
        23 => ['Coklat Bubuk',      'gram',   55],
        24 => ['Mentega',           'gram',   75],
        25 => ['Keju Parut',        'gram',   55],
        26 => ['Sirup Vanila',      'ml',     55],
        27 => ['Biji Kopi Robusta', 'kg',     2],
        28 => ['Biji Kopi Arabica', 'kg',     1.5],
        29 => ['Susu Full Cream',   'liter',  10],
        30 => ['Teh Hitam',         'gram',   40],
        31 => ['Cokelat Bubuk',     'gram',   55],
        32 => ['Krimer Bubuk',      'gram',   55],
        33 => ['Sirup Vanilla',     'ml',     55],
        34 => ['Sirup Caramel',     'ml',     40],
        35 => ['Roti Tawar',        'pcs',    5],
        36 => ['Es Batu',           'kg',     20],
        37 => ['Air Mineral',       'liter',  35],
        38 => ['Kental Manis',      'sachet', 7],
        41 => ['Jeruk Nipis',       'pcs',    10],
        42 => ['Matcha Bubuk',      'gram',   40],
        43 => ['Mie Instan',        'pcs',    7],
        44 => ['Ayam',              'gram',   200],
        45 => ['Beras',             'gram',   400],
        46 => ['Kentang',           'gram',   200],
        47 => ['Tempe',             'gram',   200],
        48 => ['Udang',             'gram',   100],
    ];

    private array $largeStock = [
        12 => 50000,   13 => 600000,  14 => 120000,  15 => 8000,
        16 => 50000,   17 => 100000,  18 => 5000,    19 => 150000,
        20 => 80000,   21 => 30000,   22 => 80000,   23 => 30000,
        24 => 40000,   25 => 30000,   26 => 30000,   27 => 500,
        28 => 300,     29 => 1000,    30 => 20000,   31 => 30000,
        32 => 30000,   33 => 30000,   34 => 20000,   35 => 2000,
        36 => 5000,    37 => 10000,   38 => 5000,    41 => 5000,
        42 => 20000,   43 => 3000,    44 => 200000,  45 => 500000,
        46 => 200000,  47 => 200000,  48 => 100000,
    ];

    public function run(): void
    {
        // ── Hapus data historis lama ──────────────────────────────────────
        $this->command->info('Menghapus data historis lama...');

        DB::statement("
            DELETE FROM order_items
            WHERE order_id IN (
                SELECT id FROM orders
                WHERE order_type = 'cashier'
                  AND status = 'selesai'
                  AND created_at >= '2025-01-01'
            )
        ");
        DB::table('orders')
            ->where('order_type', 'cashier')
            ->where('status', 'selesai')
            ->where('created_at', '>=', '2025-01-01')
            ->delete();
        DB::table('daily_ingredient_usages')
            ->where('usage_date', '>=', '2025-01-01')
            ->delete();
        DB::table('stock_adjustments')
            ->where('reason', 'like', '%stok awal%')
            ->delete();

        $this->command->info('Data lama berhasil dihapus.');

        $start    = Carbon::parse('2025-10-01');
        $end      = Carbon::parse('2026-05-22');
        $current  = $start->copy();
        $dayIndex = 0;

        $totalOrders = 0;
        $totalItems  = 0;
        $totalUsages = 0;

        $cashiers = [2, 4];
        $tables   = [1, 2, 3, 4, 5, null, null];
        $payments = ['qris', 'qris', 'qris', 'cash'];

        $this->command->info('Generate data stabil 2025-10-01 s/d 2026-05-22...');

        while ($current->lte($end)) {
            $dateStr    = $current->format('Y-m-d');
            $dateSuffix = $current->format('Ymd');
            $isWeekend  = in_array($current->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);

            // Seed RNG per hari → data reproducible & deterministik
            mt_srand($dayIndex * 9973 + 4321);

            // ── Bangun item pool harian ───────────────────────────────────
            // Setiap menu dijamin masuk pool sesuai base qty-nya.
            // Variasi kecil ±1 dengan distribusi 20/60/20 menggunakan mt_rand.
            $itemPool = [];
            foreach ($this->menus as $menuId => [$price, $qtyWD, $qtyWE]) {
                $base = $isWeekend ? $qtyWE : $qtyWD;

                // Offset ∈ {-1, 0, 0, 0, +1} → distribusi 20%/60%/20%
                $r      = mt_rand(0, 4);
                $offset = ($r === 0) ? -1 : (($r === 4) ? 1 : 0);

                // Weekend base=0: boleh tetap 0 (jarang terjual)
                // Semua case lain: minimal 1
                $qty = ($base === 0)
                    ? max(0, $base + $offset)
                    : max(1, $base + $offset);

                for ($q = 0; $q < $qty; $q++) {
                    $itemPool[] = ['menu_id' => $menuId, 'price' => $price];
                }
            }

            // Jika pool kosong (tidak ada menu yg terjual hari ini — hanya bisa terjadi
            // di weekend jika semua menu punya base=0), skip hari ini
            if (empty($itemPool)) {
                $dayIndex++;
                $current->addDay();
                continue;
            }

            // Fisher-Yates shuffle deterministik
            $n = count($itemPool);
            for ($i = $n - 1; $i > 0; $i--) {
                $j = mt_rand(0, $i);
                [$itemPool[$i], $itemPool[$j]] = [$itemPool[$j], $itemPool[$i]];
            }

            // ── Distribusikan pool ke dalam orders (1–3 item/order) ───────
            $pos    = 0;
            $seqNum = 1;

            while ($pos < $n) {
                $size  = mt_rand(1, 3);
                $slice = array_slice($itemPool, $pos, min($size, $n - $pos));
                $pos  += count($slice);

                // Merge menu yang sama dalam satu order → hindari duplikat di DB
                $merged = [];
                foreach ($slice as $item) {
                    $mid = $item['menu_id'];
                    if (!isset($merged[$mid])) {
                        $merged[$mid] = ['price' => $item['price'], 'qty' => 0];
                    }
                    $merged[$mid]['qty']++;
                }

                $total = 0;
                foreach ($merged as $info) {
                    $total += $info['qty'] * $info['price'];
                }

                $hour = mt_rand(10, 21);
                $min  = mt_rand(0, 59);
                $ts   = $current->copy()
                                ->setTime($hour, $min, mt_rand(0, 59))
                                ->format('Y-m-d H:i:s');

                $code    = 'SED-' . $dateSuffix . '-' . str_pad($seqNum++, 4, '0', STR_PAD_LEFT);
                $orderId = DB::table('orders')->insertGetId([
                    'order_code'     => $code,
                    'table_id'       => $tables[($seqNum - 2) % count($tables)],
                    'cashier_id'     => $cashiers[($seqNum - 2) % 2],
                    'order_type'     => 'cashier',
                    'status'         => 'selesai',
                    'is_paid'        => true,
                    'payment_method' => $payments[($seqNum - 2) % 4],
                    'total_amount'   => $total,
                    'created_at'     => $ts,
                    'updated_at'     => $ts,
                ]);

                foreach ($merged as $mid => $info) {
                    DB::table('order_items')->insert([
                        'order_id'   => $orderId,
                        'menu_id'    => $mid,
                        'quantity'   => $info['qty'],
                        'unit_price' => $info['price'],
                        'subtotal'   => $info['qty'] * $info['price'],
                        'created_at' => $ts,
                        'updated_at' => $ts,
                    ]);
                }

                $totalOrders++;
                $totalItems += count($merged);
            }

            // ── Daily Ingredient Usages ───────────────────────────────────
            // Weekday 25% lebih banyak dari weekend. Variasi ±8% via sine.
            $weekdayScale = $isWeekend ? 0.75 : 1.0;
            $usageTs      = $current->copy()->setTime(23, 30, 0)->format('Y-m-d H:i:s');
            $usageBatch   = [];

            foreach ($this->ingredients as $ingId => [$ingName, $ingUnit, $baseDaily]) {
                $sineVar = sin($dayIndex * M_PI / 7 + $ingId * 0.5) * 0.08;
                $amount  = $baseDaily * (1.0 + $sineVar) * $weekdayScale;

                $usageBatch[] = [
                    'usage_date'       => $dateStr,
                    'ingredient_id'    => $ingId,
                    'ingredient_name'  => $ingName,
                    'unit'             => $ingUnit,
                    'jumlah_digunakan' => round(max(0.1, $amount), 2),
                    'created_at'       => $usageTs,
                    'updated_at'       => $usageTs,
                ];
            }

            DB::table('daily_ingredient_usages')->insert($usageBatch);
            $totalUsages += count($usageBatch);

            $dayIndex++;
            $current->addDay();
        }

        $this->command->info("Orders ditambahkan  : $totalOrders");
        $this->command->info("Order items         : $totalItems");
        $this->command->info("Ingredient usages   : $totalUsages records");

        $this->command->info('Menambahkan stok awal besar...');
        $this->addLargeStock();
        $this->command->info('Selesai! Semua data historis berhasil diinsert.');
    }

    private function addLargeStock(): void
    {
        $now = now()->format('Y-m-d H:i:s');

        foreach ($this->largeStock as $ingId => $qty) {
            DB::table('stock_adjustments')->insert([
                'ingredient_id'       => $ingId,
                'ingredient_batch_id' => null,
                'adjustment_type'     => 'increase',
                'quantity'            => $qty,
                'quantity_before'     => 0,
                'quantity_after'      => $qty,
                'reason'              => 'Penambahan stok awal — persiapan expo',
                'reference'           => null,
                'recorded_by'         => 2,
                'approved_by'         => 1,
                'adjusted_at'         => $now,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        }

        $this->command->info('Stok awal berhasil ditambahkan untuk ' . count($this->largeStock) . ' bahan baku.');
    }
}
