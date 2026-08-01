<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        Menu::truncate();

        $coffee = Category::where('name', 'Coffee Base')->value('id');
        $tea = Category::where('name', 'Tea Base')->value('id');
        $lime = Category::where('name', 'Lime Base')->value('id');
        $choco = Category::where('name', 'Chocolatos Base')->value('id');
        $snack = Category::where('name', 'Snack')->value('id');
        $indomie = Category::where('name', 'Indomie Base')->value('id');
        $nasgor = Category::where('name', 'Nasi Goreng')->value('id');
        $nastel = Category::where('name', 'Nasi Telur')->value('id');
        $geprek = Category::where('name', 'Ayam Geprek')->value('id');

        // [category_id, name, price, cashback]
        $menus = [
            // ── COFFEE BASE ────────────────────────────────────
            [$coffee, 'Espresso',             10000, 2000],
            [$coffee, 'Americano Panas',      10000, 2000],
            [$coffee, 'Es Americano',         12000, 2000],
            [$coffee, 'Kopi Susu',            14000, 2000],

            // ── TEA BASE ───────────────────────────────────────
            [$tea,    'Teh Tawar',             3000, 1000],
            [$tea,    'Teh Manis',             4000, 1000],
            [$tea,    'Teh Susu',              7000, 2000],

            // ── LIME BASE ──────────────────────────────────────
            [$lime,   'Jeruk Nipis',           5000, 1000],
            [$lime,   'Teh Jeruk (Lime Tea)',  6000, 1000],

            // ── CHOCOLATOS BASE ───────────────────────────────
            [$choco,  'Full Chocolate',        8000, 2000],
            [$choco,  'Matcha',                8000, 2000],
            [$choco,  'Vanilla Latte',         8000, 2000],
            [$choco,  'Creamy Chocolatey',     8000, 2000],

            // ── SNACK ─────────────────────────────────────────
            [$snack,  'Pisang Coklat Keju',   10000, 2000],
            [$snack,  'Tempe Mendoan',         8000, 2000],
            [$snack,  'Kentang (French Fries)',12000, 2000],

            // ── INDOMIE BASE ──────────────────────────────────
            [$indomie, 'Mie Goreng Telur',    10000, 1000],
            [$indomie, 'Mie Rebus Telur',     10000, 1000],

            // ── NASI GORENG ───────────────────────────────────
            [$nasgor, 'Nasgor Telur',         12000, 2000],
            [$nasgor, 'Nasgor Ayam/Udang',    17000, 2000],

            // ── NASI TELUR ────────────────────────────────────
            [$nastel, 'Nasi Telur Saus',       9000, 1000],
            [$nastel, 'Nasi Telur Kecap',      8000, 1000],

            // ── AYAM GEPREK ───────────────────────────────────
            [$geprek, 'Nasi Ayam Geprek',     14000, 2000],
        ];

        foreach ($menus as [$catId, $name, $price, $cashback]) {
            $menu = Menu::create([
                'category_id' => $catId,
                'name' => $name,
                'price' => $price,
                'cashback' => $cashback,
                'image' => null,
                'is_available' => true,
                'is_student_discount' => true,
                'student_price' => $price - $cashback,
            ]);

            // Buat ingredient dengan nama yang sama
            $ingredient = Ingredient::create([
                'name' => $name,
                'unit' => 'pcs',
                'low_stock_threshold' => 5,
                'batch_mode' => 'fefo',
            ]);

            // Link ingredient ke menu sebagai resep 1:1
            MenuIngredient::create([
                'menu_id' => $menu->id,
                'ingredient_id' => $ingredient->id,
                'quantity_used' => 1,
            ]);

            // Buat batch stok awal
            IngredientBatch::create([
                'ingredient_id' => $ingredient->id,
                'quantity' => 50,
                'initial_quantity' => 50,
                'cost_per_unit' => $price * 0.3,
                'total_cost' => 50 * ($price * 0.3),
                'supplier_name' => 'Toko Bahan Kue Sari',
                'payment_status' => 'lunas',
                'received_at' => now(),
                'expiry_date' => now()->addMonths(6),
            ]);
        }
    }
}
