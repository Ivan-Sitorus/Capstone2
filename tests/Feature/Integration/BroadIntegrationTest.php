<?php

namespace Tests\Feature\Integration;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_creates_accurate_stock_movement_records(): void
    {
        $category = Category::create(['name' => 'Minuman']);
        $menu = Menu::create([
            'name' => 'Test Menu',
            'price' => 10000,
            'category_id' => $category->id,
        ]);
        $ingredient = Ingredient::create([
            'name' => 'Test Bahan',
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

        app(InventoryService::class)->decreaseStockForOrder([
            ['menu_id' => $menu->id, 'quantity' => 2],
        ]);

        $movement = StockMovement::where('ingredient_id', $ingredient->id)->first();
        $this->assertNotNull($movement);
        $this->assertSame(100.0, (float) $movement->quantity_before);
        $this->assertSame(-20.0, (float) $movement->quantity_change);
        $this->assertSame(80.0, (float) $movement->quantity_after);
    }

    public function test_order_to_stock_deducts_ingredients_and_records_daily_usage(): void
    {
        $category = Category::create(['name' => 'Minuman']);
        $menu = Menu::create([
            'name' => 'Test Menu',
            'price' => 10000,
            'category_id' => $category->id,
        ]);
        $ingredient = Ingredient::create([
            'name' => 'Test Bahan',
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
            'quantity_used' => 25,
        ]);

        app(InventoryService::class)->decreaseStockForOrder([
            ['menu_id' => $menu->id, 'quantity' => 2],
        ]);

        $this->assertSame(50.0, (float) $batch->fresh()->quantity);

        $this->assertDatabaseHas('daily_ingredient_usages', [
            'ingredient_id' => $ingredient->id,
        ]);
    }
}
