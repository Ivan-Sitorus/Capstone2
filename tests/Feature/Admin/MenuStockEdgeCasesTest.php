<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\MenuStock;
use App\Models\MenuStockAdjustment;
use App\Models\MenuStockBatch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\MenuStockReconciliationService;
use App\Services\MenuStockService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class MenuStockEdgeCasesTest extends TestCase
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

    public function test_auto_creates_menu_stock_when_recipe_removed(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Tepung Terigu',
            'unit' => 'gram',
            'low_stock_threshold' => 100,
            'is_active' => true,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 500,
            'received_at' => now(),
            'cost_per_unit' => 0.05,
        ]);

        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Roti Bakar Special',
            'price' => 12000,
            'is_stock_calculated' => true,
        ]);

        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 50,
        ]);

        $this->assertNull($menu->menuStock()->first());

        MenuIngredient::where('menu_id', $menu->id)->delete();
        $menu->is_stock_calculated = false;
        $menu->save();

        $menu->refresh();
        $menuStock = $menu->menuStock()->first();

        $this->assertNotNull($menuStock);
        $this->assertSame('pcs', $menuStock->unit);
        $this->assertSame(MenuStock::BATCH_MODE_FEFO, $menuStock->batch_mode);
    }

    public function test_handles_soft_delete_cascade(): void
    {
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Teh Botol',
            'price' => 5000,
            'is_stock_calculated' => false,
        ]);

        $menuStockId = $menu->menuStock->id;
        $this->assertNotNull($menu->menuStock);

        $menu->delete();

        $this->assertSoftDeleted('menus', ['id' => $menu->id]);
        $this->assertSoftDeleted('menu_stocks', ['id' => $menuStockId]);

        $menu->restore();

        $menuStock = MenuStock::withTrashed()->find($menuStockId);
        $this->assertNotNull($menuStock);
        $this->assertNull($menuStock->deleted_at);
    }

    public function test_prevents_dual_deduction(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Kopi Bubuk',
            'unit' => 'gram',
            'low_stock_threshold' => 50,
            'is_active' => true,
        ]);

        $ingredientBatch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 100,
            'received_at' => now(),
            'cost_per_unit' => 1.0,
        ]);

        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Kopi Hitam',
            'price' => 8000,
            'is_stock_calculated' => true,
        ]);

        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 10,
        ]);

        $menuStock = MenuStock::create([
            'menu_id' => $menu->id,
            'unit' => 'pcs',
            'batch_mode' => MenuStock::BATCH_MODE_FEFO,
        ]);

        $menuStockBatch = $menuStock->batches()->create([
            'quantity' => 50,
            'received_at' => now(),
            'cost_per_unit' => 3000,
        ]);

        /** @var User $cashier */
        $cashier = User::factory()->create(['role' => 'cashier']);

        $order = Order::create([
            'order_code' => 'ORD-DUAL-001',
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
            'quantity' => 1,
            'unit_price' => 8000,
            'subtotal' => 8000,
        ]);

        $service = app(InventoryService::class);
        $result = $service->decreaseStockForOrder([
            [
                'menu_id' => $menu->id,
                'quantity' => 1,
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'recorded_by' => $cashier->id,
                'reference' => $order->order_code,
            ],
        ]);

        $this->assertTrue($result['success']);

        $ingredientBatch->refresh();
        $this->assertSame(90.0, (float) $ingredientBatch->quantity);

        $menuStockBatch->refresh();
        $this->assertSame(50.0, (float) $menuStockBatch->quantity);
    }

    public function test_handles_mixed_order_rollback(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Gula Pasir',
            'unit' => 'gram',
            'low_stock_threshold' => 50,
            'is_active' => true,
        ]);

        $ingredientBatch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 10,
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
            'quantity_used' => 1,
        ]);

        $stockMenu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Brownies Coklat',
            'price' => 10000,
            'is_stock_calculated' => false,
        ]);

        $menuStockBatch = $stockMenu->menuStock->batches()->create([
            'quantity' => 1,
            'received_at' => now(),
            'cost_per_unit' => 5000,
        ]);

        /** @var User $cashier */
        $cashier = User::factory()->create(['role' => 'cashier']);

        $order = Order::create([
            'order_code' => 'ORD-ROLLBACK-001',
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
            'quantity' => 1,
            'unit_price' => 6000,
            'subtotal' => 6000,
        ]);

        $stockItem = OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $stockMenu->id,
            'quantity' => 5,
            'unit_price' => 10000,
            'subtotal' => 50000,
        ]);

        try {
            $service = app(InventoryService::class);
            $service->decreaseStockForOrder([
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
                    'quantity' => 5,
                    'order_id' => $order->id,
                    'order_item_id' => $stockItem->id,
                    'recorded_by' => $cashier->id,
                    'reference' => $order->order_code,
                ],
            ]);
            $this->fail('Expected Exception was not thrown');
        } catch (Exception $e) {
            $this->assertStringContainsString('Stok menu tidak mencukupi', $e->getMessage());
        }

        $ingredientBatch->refresh();
        $this->assertSame(10.0, (float) $ingredientBatch->quantity);

        $menuStockBatch->refresh();
        $this->assertSame(1.0, (float) $menuStockBatch->quantity);
    }

    public function test_is_idempotent_on_menu_save(): void
    {
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Teh Hangat',
            'price' => 3000,
            'is_stock_calculated' => false,
        ]);

        $this->assertDatabaseCount('menu_stocks', 1);

        $menu->name = 'Teh Hangat';
        $menu->save();

        $this->assertDatabaseCount('menu_stocks', 1);
    }

    public function test_handles_batch_exhaustion_to_zero(): void
    {
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Chips Kentang',
            'price' => 5000,
            'is_stock_calculated' => false,
        ]);

        $menuStock = $menu->menuStock;
        $batch = $menuStock->batches()->create([
            'quantity' => 5,
            'received_at' => now(),
            'cost_per_unit' => 2000,
        ]);

        $service = app(MenuStockService::class);
        $result = $service->deductMenuStockBatch(
            menuStockId: $menuStock->id,
            requiredQuantity: 5,
        );

        $this->assertSame(5.0, $result['total_deducted']);

        $batch->refresh();
        $this->assertSame(0.0, (float) $batch->quantity);

        $found = MenuStockBatch::query()
            ->where('id', $batch->id)
            ->where('quantity', '>', 0)
            ->exists();

        $this->assertFalse($found);
    }

    public function test_rejects_negative_quantity_in_adjustment(): void
    {
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Snack Box',
            'price' => 5000,
            'is_stock_calculated' => false,
        ]);

        $menuStock = $menu->menuStock;
        $menuStock->batches()->create([
            'quantity' => 10,
            'received_at' => now(),
            'cost_per_unit' => 1000,
        ]);

        $service = app(MenuStockReconciliationService::class);

        try {
            $service->createManualAdjustment(
                menuStockId: $menuStock->id,
                quantity: -1,
                adjustmentType: MenuStockAdjustment::TYPE_DECREASE,
                reason: 'Negative test',
            );
            $this->fail('Expected RuntimeException was not thrown for quantity=-1');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('harus lebih dari 0', $e->getMessage());
        }

        try {
            $service->createManualAdjustment(
                menuStockId: $menuStock->id,
                quantity: 0,
                adjustmentType: MenuStockAdjustment::TYPE_DECREASE,
                reason: 'Zero test',
            );
            $this->fail('Expected RuntimeException was not thrown for quantity=0');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('harus lebih dari 0', $e->getMessage());
        }

        $this->assertDatabaseCount('menu_stock_adjustments', 0);
    }
}
