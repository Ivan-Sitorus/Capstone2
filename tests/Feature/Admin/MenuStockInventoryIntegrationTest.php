<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\InventoryService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuStockInventoryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);
    }

    public function test_deducts_menu_stock_for_no_recipe_menu_in_order(): void
    {
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Kentang Goreng',
            'price' => 15000,
            'is_stock_calculated' => false,
        ]);

        $menuStock = $menu->menuStock;
        $batch = $menuStock->batches()->create([
            'quantity' => 10,
            'received_at' => now(),
            'cost_per_unit' => 4000,
        ]);

        /** @var User $cashier */
        $cashier = User::factory()->create(['role' => 'cashier']);

        $order = Order::create([
            'order_code' => 'ORD-TEST-001',
            'customer_name' => 'Test',
            'cashier_id' => $cashier->id,
            'status' => 'pending',
            'order_type' => 'cashier',
            'payment_method' => 'cash',
            'is_paid' => false,
            'total_amount' => 30000,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'quantity' => 2,
            'unit_price' => 15000,
            'subtotal' => 30000,
        ]);

        $service = app(InventoryService::class);
        $result = $service->decreaseStockForOrder([
            [
                'menu_id' => $menu->id,
                'quantity' => 2,
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'recorded_by' => $cashier->id,
                'reference' => $order->order_code,
            ],
        ]);

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['changes']);

        $batch->refresh();
        $this->assertSame(8.0, (float) $batch->quantity);
    }

    public function test_skips_menu_with_neither_recipe_nor_stock(): void
    {
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Roti Bakar Polos',
            'price' => 8000,
            'is_stock_calculated' => false,
        ]);

        $menu->menuStock()->delete();

        /** @var User $cashier */
        $cashier = User::factory()->create(['role' => 'cashier']);

        $order = Order::create([
            'order_code' => 'ORD-TEST-002',
            'customer_name' => 'Test',
            'cashier_id' => $cashier->id,
            'status' => 'pending',
            'order_type' => 'cashier',
            'payment_method' => 'cash',
            'is_paid' => false,
            'total_amount' => 8000,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'quantity' => 2,
            'unit_price' => 8000,
            'subtotal' => 16000,
        ]);

        $service = app(InventoryService::class);

        $result = $service->decreaseStockForOrder([
            [
                'menu_id' => $menu->id,
                'quantity' => 2,
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'recorded_by' => $cashier->id,
                'reference' => $order->order_code,
            ],
        ]);

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['changes'], 'No stock changes for menu with neither recipe nor stock');
    }

    public function test_handles_mixed_order_with_recipe_and_no_recipe_items(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Tepung Terigu',
            'unit' => 'gram',
            'low_stock_threshold' => 100,
            'is_active' => true,
        ]);

        $ingredientBatch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 1000,
            'expiry_date' => '2026-12-31',
            'received_at' => now(),
            'cost_per_unit' => 0.05,
        ]);

        $recipeMenu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Roti Bakar Spesial',
            'price' => 12000,
            'is_stock_calculated' => true,
        ]);

        MenuIngredient::create([
            'menu_id' => $recipeMenu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 50,
        ]);

        $stockMenu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Teh Botol',
            'price' => 5000,
            'is_stock_calculated' => false,
        ]);

        $stockBatch = $stockMenu->menuStock->batches()->create([
            'quantity' => 20,
            'received_at' => now(),
            'cost_per_unit' => 3500,
        ]);

        /** @var User $cashier */
        $cashier = User::factory()->create(['role' => 'cashier']);

        $order = Order::create([
            'order_code' => 'ORD-TEST-003',
            'customer_name' => 'Test',
            'cashier_id' => $cashier->id,
            'status' => 'pending',
            'order_type' => 'cashier',
            'payment_method' => 'cash',
            'is_paid' => false,
            'total_amount' => 22000,
        ]);

        $recipeItem = OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $recipeMenu->id,
            'quantity' => 1,
            'unit_price' => 12000,
            'subtotal' => 12000,
        ]);

        $stockItem = OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $stockMenu->id,
            'quantity' => 2,
            'unit_price' => 5000,
            'subtotal' => 10000,
        ]);

        $service = app(InventoryService::class);
        $result = $service->decreaseStockForOrder([
            [
                'menu_id' => $recipeMenu->id,
                'quantity' => 1,
                'order_id' => $order->id,
                'order_item_id' => $recipeItem->id,
                'recorded_by' => $cashier->id,
                'reference' => $order->order_code,
            ],
            [
                'menu_id' => $stockMenu->id,
                'quantity' => 2,
                'order_id' => $order->id,
                'order_item_id' => $stockItem->id,
                'recorded_by' => $cashier->id,
                'reference' => $order->order_code,
            ],
        ]);

        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['changes'], 'Both recipe and no-recipe items should be processed');

        $ingredientBatch->refresh();
        $this->assertSame(950.0, (float) $ingredientBatch->quantity, 'Ingredient: 1000 - (50*1) = 950');

        $stockBatch->refresh();
        $this->assertSame(18.0, (float) $stockBatch->quantity, 'Menu stock: 20 - 2 = 18');
    }

    public function test_rolls_back_mixed_order_if_menu_stock_insufficient(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Gula Pasir',
            'unit' => 'gram',
            'low_stock_threshold' => 50,
            'is_active' => true,
        ]);

        $ingredientBatch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 500,
            'expiry_date' => '2026-12-31',
            'received_at' => now(),
            'cost_per_unit' => 0.02,
        ]);

        $recipeMenu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Es Teh Manis',
            'price' => 6000,
            'is_stock_calculated' => true,
        ]);

        MenuIngredient::create([
            'menu_id' => $recipeMenu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 20,
        ]);

        $stockMenu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Brownies Coklat',
            'price' => 10000,
            'is_stock_calculated' => false,
        ]);

        $stockBatch = $stockMenu->menuStock->batches()->create([
            'quantity' => 1,
            'received_at' => now(),
            'cost_per_unit' => 5000,
        ]);

        /** @var User $cashier */
        $cashier = User::factory()->create(['role' => 'cashier']);

        $order = Order::create([
            'order_code' => 'ORD-TEST-004',
            'customer_name' => 'Test',
            'cashier_id' => $cashier->id,
            'status' => 'pending',
            'order_type' => 'cashier',
            'payment_method' => 'cash',
            'is_paid' => false,
            'total_amount' => 56000,
        ]);

        $recipeItem = OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $recipeMenu->id,
            'quantity' => 3,
            'unit_price' => 6000,
            'subtotal' => 18000,
        ]);

        $stockItem = OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $stockMenu->id,
            'quantity' => 5,
            'unit_price' => 10000,
            'subtotal' => 50000,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Stok menu tidak mencukupi');

        $service = app(InventoryService::class);
        $service->decreaseStockForOrder([
            [
                'menu_id' => $recipeMenu->id,
                'quantity' => 3,
                'order_id' => $order->id,
                'order_item_id' => $recipeItem->id,
                'recorded_by' => $cashier->id,
                'reference' => $order->order_code,
            ],
            [
                'menu_id' => $stockMenu->id,
                'quantity' => 5,
                'order_id' => $order->id,
                'order_item_id' => $stockItem->id,
                'recorded_by' => $cashier->id,
                'reference' => $order->order_code,
            ],
        ]);

        // Transaction should have rolled back — both batches unchanged
        $ingredientBatch->refresh();
        $this->assertSame(500.0, (float) $ingredientBatch->quantity, 'Ingredient batch should be unchanged after rollback');

        $stockBatch->refresh();
        $this->assertSame(1.0, (float) $stockBatch->quantity, 'Menu stock batch should be unchanged after rollback');
    }
}
