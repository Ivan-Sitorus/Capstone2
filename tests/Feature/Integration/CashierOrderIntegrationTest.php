<?php

namespace Tests\Feature\Integration;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CashierOrderIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_order_deducts_stock_and_creates_movement(): void
    {
        Cache::flush();
        $cashier = User::factory()->create(['role' => 'cashier']);
        $category = Category::create(['name' => 'Minuman']);
        $menu = Menu::create([
            'name' => 'Kopi Susu',
            'price' => 12000,
            'category_id' => $category->id,
        ]);
        $ingredient = Ingredient::create([
            'name' => 'Kopi Bubuk',
            'unit' => 'gram',
            'low_stock_threshold' => 10,
        ]);
        $batch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 100,
            'expiry_date' => now()->addDays(30),
            'received_at' => now(),
            'cost_per_unit' => 1000,
        ]);
        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 10,
        ]);

        $this->actingAs($cashier);

        $response = $this->post(route('kasir.pesanan-baru.simpan'), [
            'items' => [
                ['menu_id' => $menu->id, 'quantity' => 2],
            ],
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHas('success');

        $this->assertSame(80.0, (float) $batch->fresh()->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'ingredient_id' => $ingredient->id,
            'quantity_before' => 100,
            'quantity_change' => -20,
            'quantity_after' => 80,
        ]);
        $this->assertDatabaseHas('daily_ingredient_usages', [
            'ingredient_id' => $ingredient->id,
        ]);
    }

    public function test_cashier_order_fails_when_stock_insufficient(): void
    {
        Cache::flush();
        $cashier = User::factory()->create(['role' => 'cashier']);
        $category = Category::create(['name' => 'Minuman']);
        $menu = Menu::create([
            'name' => 'Kopi Susu',
            'price' => 12000,
            'category_id' => $category->id,
        ]);
        $ingredient = Ingredient::create([
            'name' => 'Kopi Bubuk',
            'unit' => 'gram',
            'low_stock_threshold' => 10,
        ]);
        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 15,
            'expiry_date' => now()->addDays(30),
            'received_at' => now(),
            'cost_per_unit' => 1000,
        ]);
        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 10,
        ]);

        $this->actingAs($cashier);
        $response = $this->from('/kasir/pesanan-baru')->post(route('kasir.pesanan-baru.simpan'), [
            'items' => [
                ['menu_id' => $menu->id, 'quantity' => 2],
            ],
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors('items');
    }
}
