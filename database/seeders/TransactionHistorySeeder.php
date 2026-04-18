<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Seeder data histori transaksi untuk keperluan Data Mining (Prediksi Menu).
 *
 * Dataset: 5 menu × 100 hari (2025-01-01 s/d 2025-04-10) = 500 baris
 * Sesuai dengan dataset yang digunakan dalam notebook:
 *   "1 Model Menu Prophet preprocessing_prediction.ipynb"
 *
 * Menu:
 *   - Cheesecake      Rp 27.000
 *   - Mie Goreng      Rp 28.000
 *   - Nasi Goreng     Rp 30.000
 *   - French Fries    Rp 20.000
 *   - Cappucino       Rp 22.000
 */
class TransactionHistorySeeder extends Seeder
{
    // Kuantitas per hari per menu — diambil dengan seed 42 agar reproducible.
    // Format: 100 nilai (hari 0–99) untuk tiap menu, range 1–5.
    private function generateQuantities(int $seed, int $count = 100): array
    {
        mt_srand($seed);
        $quantities = [];
        for ($i = 0; $i < $count; $i++) {
            $quantities[] = mt_rand(1, 5);
        }
        return $quantities;
    }

    public function run(): void
    {
        // ── 1. Pastikan kategori "Data Mining" ada ──────────────────────
        $category = Category::firstOrCreate(
            ['slug' => 'data-mining-seed'],
            [
                'name'      => 'Data Mining Seed',
                'slug'      => 'data-mining-seed',
                'is_active' => true,
            ]
        );

        // ── 2. Pastikan 5 menu ada ──────────────────────────────────────
        $menuData = [
            ['name' => 'Cheesecake',   'slug' => 'dm-cheesecake',   'price' => 27000],
            ['name' => 'Mie Goreng',   'slug' => 'dm-mie-goreng',   'price' => 28000],
            ['name' => 'Nasi Goreng',  'slug' => 'dm-nasi-goreng',  'price' => 30000],
            ['name' => 'French Fries', 'slug' => 'dm-french-fries', 'price' => 20000],
            ['name' => 'Cappucino',    'slug' => 'dm-cappucino',    'price' => 22000],
        ];

        $menus = [];
        foreach ($menuData as $m) {
            $menus[$m['name']] = Menu::firstOrCreate(
                ['slug' => $m['slug']],
                [
                    'category_id'         => $category->id,
                    'name'                => $m['name'],
                    'slug'                => $m['slug'],
                    'description'         => null,
                    'price'               => $m['price'],
                    'cashback'            => 0,
                    'image'               => null,
                    'is_available'        => true,
                    'is_student_discount' => false,
                    'student_price'       => null,
                ]
            );
        }

        // ── 3. Hapus data histori lama (jika seed dijalankan ulang) ─────
        $existingOrderCodes = DB::table('orders')
            ->where('order_code', 'like', 'ORD1%')
            ->whereBetween('created_at', ['2025-01-01', '2025-04-11'])
            ->pluck('id');

        if ($existingOrderCodes->isNotEmpty()) {
            DB::table('order_items')->whereIn('order_id', $existingOrderCodes)->delete();
            DB::table('orders')->whereIn('id', $existingOrderCodes)->delete();
        }

        // ── 4. Generate 500 transaksi ────────────────────────────────────
        // Seed berbeda per menu agar distribusi kuantitas bervariasi
        $menuSeeds = [
            'Cheesecake'   => 1001,
            'Mie Goreng'   => 2002,
            'Nasi Goreng'  => 3003,
            'French Fries' => 4004,
            'Cappucino'    => 5005,
        ];

        // Tanggal mulai: 2025-01-01, jumlah hari: 100
        $startDate = Carbon::create(2025, 1, 1);
        $totalDays = 100;

        // Nomor order global: ORD1000–ORD1499
        $orderNum = 1000;

        $ordersToInsert    = [];
        $orderItemsToInsert = [];
        $now               = now();

        foreach ($menuData as $menuIndex => $m) {
            $menuName   = $m['name'];
            $menuId     = $menus[$menuName]->id;
            $unitPrice  = $m['price'];
            $quantities = $this->generateQuantities($menuSeeds[$menuName], $totalDays);

            for ($day = 0; $day < $totalDays; $day++) {
                $date     = $startDate->copy()->addDays($day);
                $qty      = $quantities[$day];
                $subtotal = $qty * $unitPrice;
                $code     = 'ORD' . ($orderNum++);

                $ordersToInsert[] = [
                    'order_code'     => $code,
                    'table_id'       => null,
                    'customer_id'    => null,
                    'cashier_id'     => null,
                    'status'         => 'completed',
                    'order_type'     => 'cashier',
                    'payment_status' => 'paid',
                    'total_amount'   => $subtotal,
                    'notes'          => null,
                    'is_paid'        => true,
                    'created_at'     => $date->copy()->setTime(10, 0, 0)->toDateTimeString(),
                    'updated_at'     => $date->copy()->setTime(10, 0, 0)->toDateTimeString(),
                ];
            }
        }

        // Insert orders dalam satu batch, kemudian ambil id-nya
        foreach (array_chunk($ordersToInsert, 100) as $chunk) {
            DB::table('orders')->insert($chunk);
        }

        // Ambil order ID yang baru saja diinsert berdasarkan order_code
        $codes      = array_column($ordersToInsert, 'order_code');
        $insertedOrders = DB::table('orders')
            ->whereIn('order_code', $codes)
            ->pluck('id', 'order_code');

        // Susun order_items
        $menuIndex = 0;
        foreach ($menuData as $m) {
            $menuName   = $m['name'];
            $menuId     = $menus[$menuName]->id;
            $unitPrice  = $m['price'];
            $quantities = $this->generateQuantities($menuSeeds[$menuName], $totalDays);

            for ($day = 0; $day < $totalDays; $day++) {
                $orderNum2 = 1000 + ($menuIndex * $totalDays) + $day;
                $code      = 'ORD' . $orderNum2;
                $orderId   = $insertedOrders[$code] ?? null;

                if (! $orderId) {
                    continue;
                }

                $qty      = $quantities[$day];
                $subtotal = $qty * $unitPrice;

                $orderItemsToInsert[] = [
                    'order_id'   => $orderId,
                    'menu_id'    => $menuId,
                    'quantity'   => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal'   => $subtotal,
                    'notes'      => null,
                    'created_at' => $startDate->copy()->addDays($day)->setTime(10, 0, 0)->toDateTimeString(),
                    'updated_at' => $startDate->copy()->addDays($day)->setTime(10, 0, 0)->toDateTimeString(),
                ];
            }
            $menuIndex++;
        }

        foreach (array_chunk($orderItemsToInsert, 100) as $chunk) {
            DB::table('order_items')->insert($chunk);
        }

        $total = count($orderItemsToInsert);
        $this->command->info(
            "TransactionHistorySeeder: berhasil insert {$total} order_items " .
            "(5 menu × 100 hari, 2025-01-01 s/d 2025-04-10)."
        );
    }
}
