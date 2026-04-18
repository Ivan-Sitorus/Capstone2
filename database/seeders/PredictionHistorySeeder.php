<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Seeder data histori penjualan harian untuk keperluan Data Mining (Prediksi Menu - Prophet).
 *
 * Cakupan: 22 menu W9 Cafe × 253 hari (2025-08-01 s/d 2026-04-10)
 * Satu order per (menu, tanggal) dengan kuantitas bervariasi + weekly seasonality.
 * Order code: ORD3000–ORD9999 (tidak bertabrakan dengan ORD1xxx / ORD2xxx).
 *
 * Tujuan: memastikan Prophet memiliki data terkini untuk menghasilkan prediksi yang
 * bermakna (non-zero). AssociationHistorySeeder hanya mencakup Jan–Apr 2025 sehingga
 * Prophet harus ekstrapolasi 375+ hari ke depan → prediksi ~0.
 */
class PredictionHistorySeeder extends Seeder
{
    /** Base penjualan per hari (unit) pada weekday per menu */
    private array $baseQty = [
        'Kopi Susu'              => 12,
        'Es Americano'           => 10,
        'Americano Panas'        =>  8,
        'Espresso'               =>  6,
        'Teh Manis'              =>  9,
        'Teh Susu'               =>  7,
        'Teh Tawar'              =>  4,
        'Teh Jeruk (Lime Tea)'   =>  8,
        'Matcha'                 =>  7,
        'Vanilla Latte'          =>  9,
        'Full Chocolate'         =>  6,
        'Creamy Chocolatey'      =>  5,
        'Kentang (French Fries)' => 11,
        'Pisang Coklat Keju'     =>  9,
        'Tempe Mendoan'          =>  8,
        'Nasgor Telur'           => 10,
        'Nasgor Ayam/Udang'      =>  7,
        'Mie Goreng Telur'       =>  8,
        'Mie Rebus Telur'        =>  6,
        'Nasi Ayam Geprek'       =>  9,
        'Nasi Telur Saus'        =>  7,
        'Nasi Telur Kecap'       =>  6,
    ];

    public function run(): void
    {
        // ── Hapus data lama ────────────────────────────────────────────────
        $existingIds = DB::table('orders')
            ->where('order_code', 'like', 'ORD3%')
            ->orWhere('order_code', 'like', 'ORD4%')
            ->orWhere('order_code', 'like', 'ORD5%')
            ->orWhere('order_code', 'like', 'ORD6%')
            ->orWhere('order_code', 'like', 'ORD7%')
            ->orWhere('order_code', 'like', 'ORD8%')
            ->orWhere('order_code', 'like', 'ORD9%')
            ->pluck('id');

        if ($existingIds->isNotEmpty()) {
            DB::table('order_items')->whereIn('order_id', $existingIds)->delete();
            DB::table('orders')->whereIn('id', $existingIds)->delete();
        }

        // ── Ambil ID menu ─────────────────────────────────────────────────
        $menuNames  = array_keys($this->baseQty);
        $menuIds    = Menu::whereIn('name', $menuNames)->pluck('id', 'name');
        $menuPrices = Menu::whereIn('name', $menuNames)->pluck('price', 'name');

        $startDate = Carbon::create(2025, 8,  1);
        $endDate   = Carbon::create(2026, 4, 10);

        $orderNum   = 3000;
        $orders     = [];
        $orderItems = [];
        $now        = now()->toDateTimeString();

        $current = $startDate->copy();
        mt_srand(2025);

        while ($current->lte($endDate)) {
            $isWeekend = $current->isWeekend();

            foreach ($menuNames as $menuName) {
                $menuId = $menuIds[$menuName] ?? null;
                if (! $menuId) {
                    continue;
                }

                $base      = $this->baseQty[$menuName];
                $weekendBoost = $isWeekend ? 1.25 : 1.0;
                // Variasi acak ± 30 % dari base
                $noise     = (int) round($base * 0.30);
                $qty       = max(1, (int) round(($base + mt_rand(-$noise, $noise)) * $weekendBoost));
                $unitPrice = (float) ($menuPrices[$menuName] ?? 0);
                $subtotal  = $qty * $unitPrice;

                $hour   = mt_rand(8, 21);
                $minute = mt_rand(0, 59);
                $ts     = $current->copy()->setTime($hour, $minute, 0)->toDateTimeString();
                $code   = 'ORD' . $orderNum++;

                $orders[] = [
                    'order_code'     => $code,
                    'table_id'       => null,
                    'cashier_id'     => null,
                    'status'         => 'selesai',
                    'order_type'     => 'cashier',
                    'payment_method' => 'cash',
                    'total_amount'   => $subtotal,
                    'notes'          => null,
                    'is_paid'        => true,
                    'created_at'     => $ts,
                    'updated_at'     => $ts,
                ];
                $orderItems[$code] = [
                    'menu_id'    => $menuId,
                    'quantity'   => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal'   => $subtotal,
                    'notes'      => null,
                    'created_at' => $ts,
                    'updated_at' => $ts,
                ];
            }

            $current->addDay();
        }

        // ── Batch insert orders ────────────────────────────────────────────
        foreach (array_chunk($orders, 200) as $chunk) {
            DB::table('orders')->insert($chunk);
        }

        $codes       = array_column($orders, 'order_code');
        $insertedIds = DB::table('orders')
            ->whereIn('order_code', $codes)
            ->pluck('id', 'order_code');

        // ── Insert order_items ─────────────────────────────────────────────
        $itemRows = [];
        foreach ($orderItems as $code => $item) {
            $orderId = $insertedIds[$code] ?? null;
            if (! $orderId) {
                continue;
            }
            $item['order_id'] = $orderId;
            $itemRows[]       = $item;
        }

        foreach (array_chunk($itemRows, 500) as $chunk) {
            DB::table('order_items')->insert($chunk);
        }

        $this->command->info(
            'PredictionHistorySeeder: ' . count($orders) . ' orders, ' .
            count($itemRows) . ' order_items ' .
            '(ORD3000–ORD' . ($orderNum - 1) . ', 2025-08-01 s/d 2026-04-10, ' .
            count($menuNames) . ' menu W9 Cafe).'
        );
    }
}
