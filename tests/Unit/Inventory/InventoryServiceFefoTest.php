<?php

namespace Tests\Unit\Inventory;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceFefoTest extends TestCase
{
    use RefreshDatabase;

    public function test_fefo_deducts_soonest_expiry_first(): void
    {
        $category = Category::create(['name' => 'Minuman Test FEFO', 'slug' => 'minuman-test-fefo', 'is_active' => true]);
        $ingredient = Ingredient::create(['name' => 'Susu Test FEFO', 'unit' => 'ml', 'is_active' => true]);
        $nearExpiry = IngredientBatch::create([
            'ingredient_id' => $ingredient->id, 'quantity' => 80,
            'expiry_date' => now()->addDays(3), 'received_at' => now()->subDays(3),
        ]);
        $farExpiry = IngredientBatch::create([
            'ingredient_id' => $ingredient->id, 'quantity' => 80,
            'expiry_date' => now()->addDays(30), 'received_at' => now()->subDays(1),
        ]);
        $menu = Menu::create(['category_id' => $category->id, 'name' => 'Menu Test FEFO', 'slug' => 'menu-test-fefo', 'price' => 10000]);
        MenuIngredient::create(['menu_id' => $menu->id, 'ingredient_id' => $ingredient->id, 'quantity_used' => 30]);

        app(InventoryService::class)->decreaseStockForOrder([
            ['menu_id' => $menu->id, 'quantity' => 3],
        ]);

        $this->assertSame(0.0, (float) $nearExpiry->fresh()->quantity);
        $this->assertSame(70.0, (float) $farExpiry->fresh()->quantity);
    }

    public function test_decrease_stock_for_order_uses_fefo_batches_first(): void
    {
        $category = Category::create([
            'name' => 'Minuman Test',
            'slug' => 'minuman-test',
            'is_active' => true,
        ]);

        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Kopi Susu Test',
            'slug' => 'kopi-susu-test',
            'description' => null,
            'price' => 12000,
            'cashback' => 0,
            'image' => null,
            'is_available' => true,
            'is_student_discount' => false,
            'student_price' => null,
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Kopi Test',
            'unit' => 'gram',
            'low_stock_threshold' => 20,
            'is_active' => true,
        ]);

        $oldBatch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 100,
            'expiry_date' => now()->addDays(3)->toDateString(),
            'received_at' => now()->subDays(3),
            'cost_per_unit' => 1,
        ]);

        $newBatch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 200,
            'expiry_date' => now()->addDays(30)->toDateString(),
            'received_at' => now()->subDays(1),
            'cost_per_unit' => 1,
        ]);

        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 30,
        ]);

        $service = app(InventoryService::class);

        $result = $service->decreaseStockForOrder([
            ['menu_id' => $menu->id, 'quantity' => 2],
        ]);

        $this->assertTrue($result['success']);

        $oldBatch->refresh();
        $newBatch->refresh();

        $this->assertSame(40.0, (float) $oldBatch->quantity);
        $this->assertSame(200.0, (float) $newBatch->quantity);

        $this->assertDatabaseHas('stock_movements', [
            'ingredient_id' => $ingredient->id,
            'ingredient_batch_id' => $oldBatch->id,
            'movement_type' => 'sale',
        ]);
    }
}
