<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Seeder data histori transaksi untuk keperluan Data Mining (Association Rule / FP-Growth).
 *
 * ~840 order multi-item (2–4 menu per order) — 2025-01-01 s/d 2025-04-10.
 * Top-8 pasangan dirancang muncul 65× (≥ 6% support dari total ~1000 multi-item order),
 * sehingga FP-Growth dengan min_support=5% dapat menemukan frequent 2-itemsets dan
 * menghasilkan association rules yang bermakna.
 *
 * Kode order: ORD2000–ORD2999
 */
class AssociationHistorySeeder extends Seeder
{
    public function run(): void
    {
        // ── Hapus data histori lama ────────────────────────────────────
        $existingIds = DB::table('orders')
            ->where('order_code', 'like', 'ORD2%')
            ->pluck('id');

        if ($existingIds->isNotEmpty()) {
            DB::table('order_items')->whereIn('order_id', $existingIds)->delete();
            DB::table('orders')->whereIn('id', $existingIds)->delete();
        }

        // ── Ambil ID menu berdasarkan nama ─────────────────────────────
        $menuIds = Menu::whereIn('name', [
            'Kopi Susu', 'Es Americano', 'Americano Panas', 'Espresso',
            'Teh Manis', 'Teh Susu', 'Teh Tawar', 'Teh Jeruk (Lime Tea)',
            'Matcha', 'Vanilla Latte', 'Full Chocolate', 'Creamy Chocolatey',
            'Kentang (French Fries)', 'Pisang Coklat Keju', 'Tempe Mendoan',
            'Nasgor Telur', 'Nasgor Ayam/Udang', 'Mie Goreng Telur', 'Mie Rebus Telur',
            'Nasi Ayam Geprek', 'Nasi Telur Saus', 'Nasi Telur Kecap',
        ])->pluck('id', 'name');

        $menuPrices = Menu::whereIn('name', $menuIds->keys()->toArray())
            ->pluck('price', 'name');

        // ── Template kombinasi [menu_names, repeat_count] ─────────────
        //
        // TARGET: top-8 pasangan muncul ≥65 kali.
        // Dengan total ~1000 multi-item orders → support ≥ 6.5% > 5% ✓
        //
        // Pasangan medium (20–30×) akan menjadi frequent 1-itemsets
        // dan memberikan variasi rules di confidence tier bawah.
        $templates = [

            // ═══ TOP-8 PASANGAN — masing-masing 65× ════════════════════
            // Kombinasi alami: kopi/minuman + snack/makanan berat
            [['Kopi Susu',             'Kentang (French Fries)'],     65],
            [['Es Americano',          'Pisang Coklat Keju'],         65],
            [['Nasi Ayam Geprek',      'Es Americano'],               65],
            [['Nasgor Telur',          'Kopi Susu'],                  65],
            [['Teh Manis',             'Mie Goreng Telur'],           65],
            [['Matcha',                'Tempe Mendoan'],              65],
            [['Vanilla Latte',         'Kentang (French Fries)'],     65],
            [['Teh Jeruk (Lime Tea)',  'Nasgor Telur'],               65],

            // ═══ PASANGAN MENENGAH — 25-30× ═════════════════════════════
            [['Americano Panas',   'Pisang Coklat Keju'],             30],
            [['Full Chocolate',    'Kentang (French Fries)'],         28],
            [['Mie Rebus Telur',   'Teh Susu'],                       26],
            [['Teh Manis',         'Tempe Mendoan'],                   25],
            [['Kopi Susu',         'Pisang Coklat Keju'],              25],
            [['Creamy Chocolatey', 'Pisang Coklat Keju'],              24],
            [['Espresso',          'Kentang (French Fries)'],          22],
            [['Nasi Telur Saus',   'Teh Manis'],                       20],

            // ═══ VARIASI 3-ITEM (kombinasi grup) ════════════════════════
            [['Kopi Susu',      'Kentang (French Fries)', 'Pisang Coklat Keju'],  15],
            [['Es Americano',   'Nasi Ayam Geprek',       'Tempe Mendoan'],       12],
            [['Matcha',         'Kentang (French Fries)', 'Pisang Coklat Keju'],  12],
            [['Teh Manis',      'Mie Goreng Telur',       'Tempe Mendoan'],       10],
            [['Vanilla Latte',  'Nasi Ayam Geprek',       'Tempe Mendoan'],       10],
            [['Kopi Susu',      'Nasgor Telur',           'Tempe Mendoan'],        8],

            // ═══ VARIASI LAINNYA ═════════════════════════════════════════
            [['Nasgor Ayam/Udang', 'Kopi Susu'],         18],
            [['Kopi Susu',         'Tempe Mendoan'],      15],
            [['Teh Susu',          'Nasgor Telur'],       14],
            [['Nasi Telur Kecap',  'Teh Manis'],          12],
            [['Nasi Ayam Geprek',  'Teh Manis'],          12],
            [['Americano Panas',   'Tempe Mendoan'],      10],
            [['Mie Goreng Telur',  'Teh Tawar'],          10],
            [['Espresso',          'Pisang Coklat Keju'], 10],
        ];

        // ── Generate orders ────────────────────────────────────────────
        $startDate = Carbon::create(2025, 1, 1);
        $endDate   = Carbon::create(2025, 4, 10);
        $totalDays = $startDate->diffInDays($endDate) + 1; // 100 hari

        $orderNum   = 2000;
        $orders     = [];
        $orderItems = [];

        foreach ($templates as [$menuNames, $count]) {
            for ($i = 0; $i < $count; $i++) {
                $dayOffset = ($orderNum - 2000) % $totalDays;
                $hour      = mt_rand(8, 20);
                $minute    = mt_rand(0, 59);
                $orderDate = $startDate->copy()->addDays($dayOffset)->setTime($hour, $minute, 0);
                $orderCode = 'ORD' . $orderNum++;

                $totalAmount = 0;
                $items       = [];

                foreach ($menuNames as $menuName) {
                    $menuId    = $menuIds[$menuName] ?? null;
                    $unitPrice = (float) ($menuPrices[$menuName] ?? 0);
                    if (! $menuId) {
                        continue;
                    }
                    $qty      = mt_rand(1, 3);
                    $subtotal = $qty * $unitPrice;
                    $totalAmount += $subtotal;
                    $items[] = [
                        'menu_id'    => $menuId,
                        'quantity'   => $qty,
                        'unit_price' => $unitPrice,
                        'subtotal'   => $subtotal,
                        'notes'      => null,
                        'created_at' => $orderDate->toDateTimeString(),
                        'updated_at' => $orderDate->toDateTimeString(),
                    ];
                }

                if (empty($items)) {
                    continue;
                }

                $orders[] = [
                    'order_code'     => $orderCode,
                    'table_id'       => null,
                    'cashier_id'     => null,
                    'status'         => 'selesai',
                    'order_type'     => 'cashier',
                    'payment_method' => 'cash',
                    'total_amount'   => $totalAmount,
                    'notes'          => null,
                    'is_paid'        => true,
                    'created_at'     => $orderDate->toDateTimeString(),
                    'updated_at'     => $orderDate->toDateTimeString(),
                ];
                $orderItems[$orderCode] = $items;
            }
        }

        // ── Batch insert orders ────────────────────────────────────────
        foreach (array_chunk($orders, 100) as $chunk) {
            DB::table('orders')->insert($chunk);
        }

        $codes       = array_column($orders, 'order_code');
        $insertedIds = DB::table('orders')
            ->whereIn('order_code', $codes)
            ->pluck('id', 'order_code');

        // ── Insert order_items ─────────────────────────────────────────
        $itemRows = [];
        foreach ($orderItems as $code => $items) {
            $orderId = $insertedIds[$code] ?? null;
            if (! $orderId) {
                continue;
            }
            foreach ($items as $item) {
                $item['order_id'] = $orderId;
                $itemRows[]       = $item;
            }
        }

        foreach (array_chunk($itemRows, 200) as $chunk) {
            DB::table('order_items')->insert($chunk);
        }

        $this->command->info(
            'AssociationHistorySeeder: ' . count($orders) . ' orders, ' .
            count($itemRows) . ' order_items ' .
            '(ORD2000–ORD' . ($orderNum - 1) . ', 2025-01-01 s/d 2025-04-10).'
        );
    }
}
