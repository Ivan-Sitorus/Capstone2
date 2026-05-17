<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuStockAdjustment;
use App\Services\MenuStockReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class MenuStockReconciliationTest extends TestCase
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

    public function test_increases_stock_and_creates_movement(): void
    {
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Kopi Latte',
            'price' => 18000,
            'is_stock_calculated' => false,
        ]);

        $menuStock = $menu->menuStock;
        $batch = $menuStock->batches()->create([
            'quantity' => 10,
            'received_at' => now(),
            'cost_per_unit' => 4000,
        ]);

        $service = app(MenuStockReconciliationService::class);

        $adjustment = $service->createManualAdjustment(
            menuStockId: $menuStock->id,
            quantity: 5,
            adjustmentType: MenuStockAdjustment::TYPE_INCREASE,
            reason: 'Restock from supplier',
        );

        $batch->refresh();

        $this->assertSame(15.0, (float) $batch->quantity);
        $this->assertSame(5.0, (float) $adjustment->quantity);
        $this->assertSame(MenuStockAdjustment::TYPE_INCREASE, $adjustment->adjustment_type);
        $this->assertSame(10.0, (float) $adjustment->quantity_before);
        $this->assertSame(15.0, (float) $adjustment->quantity_after);

        $this->assertDatabaseHas('menu_stock_movements', [
            'menu_stock_adjustment_id' => $adjustment->id,
            'movement_type' => 'adjustment_increase',
        ]);
    }

    public function test_decreases_stock_with_signed_negative_quantity(): void
    {
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Matcha Latte',
            'price' => 20000,
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
            reason: 'Damaged items',
        );

        $this->assertSame(-3.0, (float) $adjustment->quantity);
        $this->assertSame(MenuStockAdjustment::TYPE_DECREASE, $adjustment->adjustment_type);
        $this->assertSame(10.0, (float) $adjustment->quantity_before);
        $this->assertSame(7.0, (float) $adjustment->quantity_after);

        $batch = $menuStock->batches()->first();
        $this->assertSame(7.0, (float) $batch->quantity);
    }

    public function test_rejects_invalid_quantity(): void
    {
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Es Jeruk',
            'price' => 7000,
            'is_stock_calculated' => false,
        ]);

        $menuStock = $menu->menuStock;
        $menuStock->batches()->create([
            'quantity' => 10,
            'received_at' => now(),
            'cost_per_unit' => 1500,
        ]);

        $service = app(MenuStockReconciliationService::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Jumlah penyesuaian harus lebih dari 0');

        $service->createManualAdjustment(
            menuStockId: $menuStock->id,
            quantity: 0,
            adjustmentType: MenuStockAdjustment::TYPE_INCREASE,
            reason: 'Zero test',
        );
    }

    public function test_rejects_negative_quantity(): void
    {
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Jus Alpukat',
            'price' => 12000,
            'is_stock_calculated' => false,
        ]);

        $menuStock = $menu->menuStock;
        $menuStock->batches()->create([
            'quantity' => 10,
            'received_at' => now(),
            'cost_per_unit' => 3000,
        ]);

        $service = app(MenuStockReconciliationService::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Jumlah penyesuaian harus lebih dari 0');

        $service->createManualAdjustment(
            menuStockId: $menuStock->id,
            quantity: -5,
            adjustmentType: MenuStockAdjustment::TYPE_DECREASE,
            reason: 'Negative test',
        );
    }
}
