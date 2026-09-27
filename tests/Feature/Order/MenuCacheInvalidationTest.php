<?php

namespace Tests\Feature\Order;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MenuCacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_per_menu_cache_is_forgotten_when_a_menu_is_deleted(): void
    {
        $category = Category::create(['name' => 'Kopi']);
        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Kopi Susu',
            'price' => 12000,
            'cost_price' => 7000,
            'status' => 'active',
        ]);

        Cache::put('customer_menu', 'cached', 300);
        Cache::put('menu_categories_cashier', 'cached', 300);
        Cache::put('menu_categories_cashier_v2', 'cached', 300);
        Cache::put("menu_{$menu->id}", 'cached', 300);

        $menu->delete();

        $this->assertFalse(Cache::has('customer_menu'));
        $this->assertFalse(Cache::has('menu_categories_cashier'));
        $this->assertFalse(Cache::has('menu_categories_cashier_v2'));
        $this->assertFalse(Cache::has("menu_{$menu->id}"));
    }
}
