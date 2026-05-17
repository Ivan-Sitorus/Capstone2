<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuStock;
use App\Models\MenuStockBatch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\MenuStockService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuStockServiceTest extends TestCase
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

    public function test_deducts_from_single_batch_correctly(): void
    {
        // Create a no-recipe menu — MenuObserver auto-creates MenuStock
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Teh Botol',
            'price' => 5000,
            'is_stock_calculated' => false,
        ]);

        $menuStock = $menu->menuStock;
        $this->assertNotNull($menuStock, 'MenuStock should be auto-created');

        // Create a batch with quantity 10
        /** @var MenuStockBatch $batch */
        $batch = $menuStock->batches()->create([
            'quantity' => 10,
            'received_at' => now(),
            'cost_per_unit' => 3500,
        ]);

        $service = app(MenuStockService::class);
        $result = $service->deductMenuStockBatch(
            menuStockId: $menuStock->id,
            requiredQuantity: 3,
            context: [],
        );

        $this->assertSame(3.0, $result['total_deducted']);
        $this->assertCount(1, $result['batch_changes']);

        $batch->refresh();
        $this->assertSame(7.0, (float) $batch->quantity);

        $this->assertDatabaseHas('menu_stock_movements', [
            'menu_stock_id' => $menuStock->id,
            'menu_stock_batch_id' => $batch->id,
            'quantity_change' => -3.0,
        ]);
    }

    public function test_deducts_across_multiple_batches_using_fefo(): void
    {
        // Create a no-recipe menu with FEFO batch mode (default)
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Kopi Robusta',
            'price' => 12000,
            'is_stock_calculated' => false,
        ]);

        $menuStock = $menu->menuStock;
        // MenuStock default batch_mode is 'fefo'

        // Batch A: expires later (2026-12-31), qty=10
        $batchA = $menuStock->batches()->create([
            'quantity' => 10,
            'expiry_date' => '2026-12-31',
            'received_at' => now()->subDays(2),
            'cost_per_unit' => 3000,
        ]);

        // Batch B: expires sooner (2026-06-15), qty=5
        $batchB = $menuStock->batches()->create([
            'quantity' => 5,
            'expiry_date' => '2026-06-15',
            'received_at' => now()->subDay(),
            'cost_per_unit' => 3500,
        ]);

        $service = app(MenuStockService::class);
        $result = $service->deductMenuStockBatch(
            menuStockId: $menuStock->id,
            requiredQuantity: 7,
        );

        $this->assertSame(7.0, $result['total_deducted']);

        // FEFO: earlier-expiring batch (B, 2026-06-15) should be deducted first
        $batchB->refresh();
        $this->assertSame(0.0, (float) $batchB->quantity, 'Earlier-expiring batch should be fully depleted first');

        $batchA->refresh();
        $this->assertSame(8.0, (float) $batchA->quantity, 'Remaining 2 should come from later-expiring batch');

        // Two movements should be created (one per batch)
        $this->assertDatabaseHas('menu_stock_movements', [
            'menu_stock_batch_id' => $batchB->id,
            'quantity_before' => 5.0,
            'quantity_change' => -5.0,
            'quantity_after' => 0.0,
        ]);

        $this->assertDatabaseHas('menu_stock_movements', [
            'menu_stock_batch_id' => $batchA->id,
            'quantity_before' => 10.0,
            'quantity_change' => -2.0,
            'quantity_after' => 8.0,
        ]);
    }

    public function test_throws_exception_when_stock_insufficient(): void
    {
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Brownies',
            'price' => 8000,
            'is_stock_calculated' => false,
        ]);

        $menuStock = $menu->menuStock;
        $menuStock->batches()->create([
            'quantity' => 5,
            'received_at' => now(),
            'cost_per_unit' => 2000,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Stok menu tidak mencukupi');

        $service = app(MenuStockService::class);
        $service->deductMenuStockBatch(
            menuStockId: $menuStock->id,
            requiredQuantity: 10,
        );
    }

    public function test_is_idempotent_for_same_order(): void
    {
        // Create an order with a no-recipe menu item
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Croissant',
            'price' => 15000,
            'is_stock_calculated' => false,
        ]);

        $menuStock = $menu->menuStock;
        $menuStock->batches()->create([
            'quantity' => 20,
            'received_at' => now(),
            'cost_per_unit' => 5000,
        ]);

        /** @var User $cashier */
        $cashier = User::factory()->create(['role' => 'cashier']);

        /** @var Order $order */
        $order = Order::create([
            'order_code' => 'ORD-TEST-IDEM',
            'customer_name' => 'Test Customer',
            'cashier_id' => $cashier->id,
            'status' => 'pending',
            'order_type' => 'cashier',
            'payment_method' => 'cash',
            'is_paid' => false,
            'total_amount' => 15000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'quantity' => 2,
            'unit_price' => 15000,
            'subtotal' => 30000,
        ]);

        $service = app(MenuStockService::class);

        // First call — should process
        $result1 = $service->processSaleForOrderMenuStock($order, $cashier->id);
        $this->assertTrue($result1['success']);
        $this->assertArrayNotHasKey('skipped', array_filter((array) $result1, fn ($v) => $v === true) ? $result1 : []);

        // Second call — should be idempotent (skipped)
        $result2 = $service->processSaleForOrderMenuStock($order, $cashier->id);
        $this->assertTrue($result2['skipped'] ?? false, 'Second call should be skipped');

        // Batch should only be deducted once
        $batch = $menuStock->batches()->first();
        $this->assertSame(18.0, (float) $batch->quantity);
    }

    public function test_handles_fifo_ordering(): void
    {
        // Create menu with FIFO batch mode
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Pisang Goreng',
            'price' => 10000,
            'is_stock_calculated' => false,
        ]);

        $menuStock = $menu->menuStock;
        $menuStock->update(['batch_mode' => MenuStock::BATCH_MODE_FIFO]);

        // Batch 1: received earlier (2026-05-10)
        $batch1 = $menuStock->batches()->create([
            'quantity' => 8,
            'received_at' => '2026-05-10 08:00:00',
            'expiry_date' => '2026-12-31',
            'cost_per_unit' => 2500,
        ]);

        // Batch 2: received later (2026-05-15)
        $batch2 = $menuStock->batches()->create([
            'quantity' => 6,
            'received_at' => '2026-05-15 08:00:00',
            'expiry_date' => '2026-06-30',
            'cost_per_unit' => 3000,
        ]);

        $service = app(MenuStockService::class);
        $result = $service->deductMenuStockBatch(
            menuStockId: $menuStock->id,
            requiredQuantity: 10,
        );

        $this->assertSame(10.0, $result['total_deducted']);

        // FIFO: earlier-received batch (1) deducted first
        $batch1->refresh();
        $this->assertSame(0.0, (float) $batch1->quantity, 'Earlier-received batch should be fully depleted first');

        $batch2->refresh();
        $this->assertSame(4.0, (float) $batch2->quantity, 'Remaining 2 should come from later-received batch');
    }
}
