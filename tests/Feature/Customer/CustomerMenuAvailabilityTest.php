<?php

namespace Tests\Feature\Customer;

use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuIngredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CustomerMenuAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Cache array bertahan antar-test dalam satu proses; bersihkan agar
        // daftar menu dari test sebelumnya tidak bocor.
        Cache::flush();
    }

    private function createOutOfStockMenu(): Menu
    {
        $category = Category::create(['name' => 'Kopi']);

        $menu = Menu::create([
            'category_id' => $category->id,
            'name'        => 'Kopi Susu',
            'price'       => 16000,
            'status'      => 'active',
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Susu UHT',
            'unit' => 'ml',
            'batch_mode' => 'fefo',
        ]);

        MenuIngredient::create([
            'menu_id'       => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 100,
            'unit'          => 'ml',
        ]);

        // Sengaja tanpa batch => stok 0 => menu habis.
        return $menu;
    }

    public function test_customer_menu_payload_marks_zero_stock_menu_as_unavailable(): void
    {
        $menu = $this->createOutOfStockMenu();

        $this->get(route('customer.menu'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Customer/Menu/Index', false)
                ->has('categories', 1)
                ->where('categories.0.menus.0.id', $menu->id)
                ->where('categories.0.menus.0.is_available', false)
                ->where('categories.0.menus.0.available_stock', 0));
    }

    public function test_customer_order_for_out_of_stock_menu_is_rejected_and_creates_no_order(): void
    {
        $menu  = $this->createOutOfStockMenu();
        $table = CafeTable::create(['table_number' => 9]);

        $response = $this->postJson(route('customer.order.store'), [
            'customer_name' => 'Budi',
            'table_id'      => $table->id,
            'items'         => [
                ['menu_id' => $menu->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', "Stok 'Susu UHT' tidak mencukupi.");

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }
}
