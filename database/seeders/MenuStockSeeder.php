<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuStock;
use App\Models\MenuStockBatch;
use Illuminate\Database\Seeder;

class MenuStockSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::first() ?? Category::create([
            'name' => 'Minuman',
            'is_active' => true,
        ]);

        $products = [
            ['name' => 'Air Mineral',   'price' => 3000,  'stock' => 48],
            ['name' => 'Teh Kotak',     'price' => 5000,  'stock' => 36],
            ['name' => 'Kopi Sachet',   'price' => 4000,  'stock' => 60],
            ['name' => 'Susu UHT',      'price' => 6000,  'stock' => 24],
            ['name' => 'Jus Kemasan',   'price' => 7000,  'stock' => 30],
        ];

        foreach ($products as $p) {
            $menu = Menu::firstOrCreate(
                ['slug' => str($p['name'])->slug()],
                [
                    'category_id' => $category->id,
                    'name' => $p['name'],
                    'price' => $p['price'],
                    'is_available' => true,
                    'is_stock_calculated' => false,
                    'cashback' => 0,
                ]
            );

            if (! $menu->menuStock) {
                MenuStock::create([
                    'menu_id' => $menu->id,
                    'unit' => 'pcs',
                    'batch_mode' => 'fefo',
                ]);
            }

            MenuStockBatch::create([
                'menu_stock_id' => $menu->menuStock->id ?? $menu->refresh()->menuStock->id,
                'quantity' => $p['stock'],
                'expiry_date' => now()->addMonths(rand(3, 8)),
                'received_at' => now()->subDays(rand(1, 14)),
            ]);
        }

        $this->command->info(count($products).' menu stocks berhasil di-seed.');
    }
}
