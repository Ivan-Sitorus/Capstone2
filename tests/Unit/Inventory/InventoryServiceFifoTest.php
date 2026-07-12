<?php

namespace Tests\Unit\Inventory;

use App\Enums\BatchMode;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceFifoTest extends TestCase
{
    use RefreshDatabase;

    public function test_decrease_stock_for_order_deducts_ingredients_by_recipe(): void
    {
        $category = Category::create(['name' => 'Minuman', 'slug' => 'minuman', 'is_active' => true]);
        $menu = Menu::create(['category_id' => $category->id, 'name' => 'Kopi Susu Test', 'slug' => 'kopi-susu-test', 'price' => 12000]);
        $ingredient = Ingredient::create(['name' => 'Kopi Test', 'unit' => 'gram', 'is_active' => true]);
        IngredientBatch::create([
            'ingredient_id' => $ingredient->id, 'quantity' => 100,
            'expiry_date' => now()->addYear(), 'received_at' => now(),
        ]);
        MenuIngredient::create([
            'menu_id' => $menu->id, 'ingredient_id' => $ingredient->id, 'quantity_used' => 30,
        ]);

        $result = app(InventoryService::class)->decreaseStockForOrder([
            ['menu_id' => $menu->id, 'quantity' => 2],
        ]);

        $this->assertTrue($result['success']);
        $this->assertDatabaseHas('stock_movements', [
            'ingredient_id' => $ingredient->id,
            'movement_type' => 'sale',
        ]);
    }

    public function test_fifo_deducts_oldest_batch_first(): void
    {
        $category = Category::create(['name' => 'Minuman Test FIFO', 'slug' => 'minuman-test-fifo', 'is_active' => true]);
        $ingredient = Ingredient::create(['name' => 'Kopi Test FIFO', 'unit' => 'gram', 'is_active' => true, 'batch_mode' => 'fifo']);
        $oldBatch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id, 'quantity' => 100,
            'received_at' => now()->subDays(5), 'expiry_date' => now()->addYear(),
        ]);
        $newBatch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id, 'quantity' => 200,
            'received_at' => now()->subDays(1), 'expiry_date' => now()->addDays(3),
        ]);
        $menu = Menu::create(['category_id' => $category->id, 'name' => 'Menu Test FIFO', 'slug' => 'menu-test-fifo', 'price' => 10000]);
        MenuIngredient::create(['menu_id' => $menu->id, 'ingredient_id' => $ingredient->id, 'quantity_used' => 30]);

        app(InventoryService::class)->decreaseStockForOrder([
            ['menu_id' => $menu->id, 'quantity' => 2],
        ]);

        $this->assertSame(40.0, (float) $oldBatch->fresh()->quantity);
        $this->assertSame(200.0, (float) $newBatch->fresh()->quantity);
    }

    public function test_decrease_stock_for_order_uses_fifo_batches_first(): void
    {
        $category = Category::create([
            'name' => 'Minuman Test FIFO',
            'slug' => 'minuman-test-fifo',
            'is_active' => true,
        ]);

        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Kopi Susu Test FIFO',
            'slug' => 'kopi-susu-test-fifo',
            'description' => null,
            'price' => 12000,
            'cashback' => 0,
            'image' => null,
            'is_available' => true,
            'is_student_discount' => false,
            'student_price' => null,
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Kopi Test FIFO',
            'unit' => 'gram',
            'low_stock_threshold' => 20,
            'is_active' => true,
            'batch_mode' => BatchMode::Fifo->value,
        ]);

        // Batch A: older received_at (5 days ago), far future expiry
        $oldBatch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 100,
            'expiry_date' => now()->addYear()->toDateString(),
            'received_at' => now()->subDays(5),
            'cost_per_unit' => 1,
        ]);

        // Batch B: newer received_at (1 day ago), near-term expiry
        // FEFO would pick this batch first (nearest expiry), but FIFO should pick Batch A (oldest received_at)
        $newBatch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 200,
            'expiry_date' => now()->addDays(3)->toDateString(),
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

        // FIFO: oldest received_at consumed first => Batch A (received 5 days ago) should drop from 100 to 40
        // Batch B (received 1 day ago) should remain untouched at 200
        $this->assertSame(40.0, (float) $oldBatch->quantity);
        $this->assertSame(200.0, (float) $newBatch->quantity);

        $this->assertDatabaseHas('stock_movements', [
            'ingredient_id' => $ingredient->id,
            'ingredient_batch_id' => $oldBatch->id,
            'movement_type' => 'sale',
        ]);
    }
}
