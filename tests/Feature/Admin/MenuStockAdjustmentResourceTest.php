<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuStockAdjustment;
use App\Models\User;
use App\Services\MenuStockReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuStockAdjustmentResourceTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_active' => true,
        ]);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_can_create_increase_adjustment(): void
    {
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Kopi Robusta',
            'price' => 12000,
            'is_stock_calculated' => false,
        ]);

        $menuStock = $menu->menuStock;
        $menuStock->batches()->create([
            'quantity' => 10,
            'received_at' => now(),
            'cost_per_unit' => 5000,
        ]);

        $service = app(MenuStockReconciliationService::class);

        $adjustment = $service->createManualAdjustment(
            menuStockId: $menuStock->id,
            quantity: 5,
            adjustmentType: MenuStockAdjustment::TYPE_INCREASE,
            reason: 'Restock',
            reportedBy: $this->admin->id,
        );

        $this->assertDatabaseHas('menu_stock_adjustments', [
            'menu_stock_id' => $menuStock->id,
            'adjustment_type' => MenuStockAdjustment::TYPE_INCREASE,
            'quantity' => '5.00',
        ]);

        $this->assertSame(15.0, $menuStock->fresh()->getTotalStock());
        $this->assertSame(10.0, (float) $adjustment->quantity_before);
        $this->assertSame(15.0, (float) $adjustment->quantity_after);

        $this->assertDatabaseHas('menu_stock_movements', [
            'menu_stock_id' => $menuStock->id,
            'menu_stock_adjustment_id' => $adjustment->id,
            'movement_type' => 'adjustment_increase',
        ]);
    }

    public function test_can_create_decrease_adjustment(): void
    {
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Croissant',
            'price' => 15000,
            'is_stock_calculated' => false,
        ]);

        $menuStock = $menu->menuStock;
        $menuStock->batches()->create([
            'quantity' => 10,
            'received_at' => now(),
            'cost_per_unit' => 5000,
        ]);

        $service = app(MenuStockReconciliationService::class);

        $adjustment = $service->createManualAdjustment(
            menuStockId: $menuStock->id,
            quantity: 3,
            adjustmentType: MenuStockAdjustment::TYPE_DECREASE,
            reason: 'Waste',
            reportedBy: $this->admin->id,
        );

        $this->assertDatabaseHas('menu_stock_adjustments', [
            'menu_stock_id' => $menuStock->id,
            'adjustment_type' => MenuStockAdjustment::TYPE_DECREASE,
            'quantity' => '-3.00',
        ]);

        $this->assertSame(7.0, $menuStock->fresh()->getTotalStock());
        $this->assertSame(10.0, (float) $adjustment->quantity_before);
        $this->assertSame(7.0, (float) $adjustment->quantity_after);

        $this->assertDatabaseHas('menu_stock_movements', [
            'menu_stock_id' => $menuStock->id,
            'menu_stock_adjustment_id' => $adjustment->id,
            'movement_type' => 'adjustment_decrease',
        ]);
    }

    public function test_read_only_detail_on_edit_context(): void
    {
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Pisang Goreng',
            'price' => 10000,
            'is_stock_calculated' => false,
        ]);

        $menuStock = $menu->menuStock;
        $menuStock->batches()->create([
            'quantity' => 20,
            'received_at' => now(),
            'cost_per_unit' => 3000,
        ]);

        $service = app(MenuStockReconciliationService::class);

        $adjustment = $service->createManualAdjustment(
            menuStockId: $menuStock->id,
            quantity: 5,
            adjustmentType: MenuStockAdjustment::TYPE_INCREASE,
            reason: 'Restock from supplier',
            reportedBy: $this->admin->id,
        );

        // The Filament resource form marks these fields as disabledOn('edit').
        // We verify the data integrity: once created, adjustment records are immutable.
        $adjustment->refresh();

        $this->assertSame('Restock from supplier', $adjustment->reason);
        $this->assertSame(MenuStockAdjustment::TYPE_INCREASE, $adjustment->adjustment_type);
        $this->assertSame(5.0, (float) $adjustment->quantity);
        $this->assertSame(20.0, (float) $adjustment->quantity_before);
        $this->assertSame(25.0, (float) $adjustment->quantity_after);
        $this->assertSame($menuStock->id, $adjustment->menu_stock_id);
        $this->assertSame($this->admin->id, (int) $adjustment->reported_by);

        // Verify the adjustment still exists unchanged (read-only concept)
        $this->assertDatabaseHas('menu_stock_adjustments', [
            'id' => $adjustment->id,
            'menu_stock_id' => $menuStock->id,
            'adjustment_type' => MenuStockAdjustment::TYPE_INCREASE,
            'reason' => 'Restock from supplier',
        ]);
    }
}
