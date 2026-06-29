<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::truncate();

        $categories = [
            ['name' => 'Coffee Base'],
            ['name' => 'Tea Base'],
            ['name' => 'Lime Base'],
            ['name' => 'Chocolatos Base'],
            ['name' => 'Snack'],
            ['name' => 'Indomie Base'],
            ['name' => 'Nasi Goreng'],
            ['name' => 'Nasi Telur'],
            ['name' => 'Ayam Geprek'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }
    }
}
