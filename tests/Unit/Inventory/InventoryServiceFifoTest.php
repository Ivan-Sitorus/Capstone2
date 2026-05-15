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

class InventoryServiceFifoTest extends TestCase
{
    use RefreshDatabase;

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
            'batch_mode' => Ingredient::BATCH_MODE_FIFO,
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
