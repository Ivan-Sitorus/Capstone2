<?php

namespace Tests\Feature\Inventory;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuWasteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Menu::unsetEventDispatcher();
    }

    public function test_waste_recipe_menu_deducts_all_ingredients(): void
    {
        $category = Category::create(['name' => 'Minuman Test']);

        $menu = Menu::create([
            'category_id' => $category->id, 'name' => 'Kopi Waste Test',
            'description' => null, 'price' => 15000, 'image' => null,
            'is_available' => true, 'is_student_discount' => false, 'student_price' => null,
        ]);

        $gula = Ingredient::create(['name' => 'Gula Test', 'unit' => 'gram', 'low_stock_threshold' => 10, 'is_active' => true]);
        $kopi = Ingredient::create(['name' => 'Kopi Test', 'unit' => 'gram', 'low_stock_threshold' => 10, 'is_active' => true]);
        $air = Ingredient::create(['name' => 'Air Test', 'unit' => 'ml', 'low_stock_threshold' => 50, 'is_active' => true]);

        IngredientBatch::create(['ingredient_id' => $gula->id, 'quantity' => 100, 'expiry_date' => now()->addDays(30), 'received_at' => now(), 'cost_per_unit' => 1]);
        IngredientBatch::create(['ingredient_id' => $kopi->id, 'quantity' => 100, 'expiry_date' => now()->addDays(30), 'received_at' => now(), 'cost_per_unit' => 1]);
        IngredientBatch::create(['ingredient_id' => $air->id, 'quantity' => 500, 'expiry_date' => now()->addDays(30), 'received_at' => now(), 'cost_per_unit' => 1]);

        MenuIngredient::create(['menu_id' => $menu->id, 'ingredient_id' => $gula->id, 'quantity_used' => 10]);
        MenuIngredient::create(['menu_id' => $menu->id, 'ingredient_id' => $kopi->id, 'quantity_used' => 15]);
        MenuIngredient::create(['menu_id' => $menu->id, 'ingredient_id' => $air->id, 'quantity_used' => 200]);

        $gulaBefore = $gula->fresh()->getTotalStock();
        $kopiBefore = $kopi->fresh()->getTotalStock();
        $airBefore = $air->fresh()->getTotalStock();

        $service = app(InventoryService::class);
        $result = $service->wasteMenu(menuId: $menu->id, quantity: 2, wasteCategory: 'expired', reason: 'Test waste', recordedBy: null);

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['adjustments']);
        $this->assertEquals(3, $result['total_ingredients_deducted']);
        $this->assertEquals($gulaBefore - 20, $gula->fresh()->getTotalStock());
        $this->assertEquals($kopiBefore - 30, $kopi->fresh()->getTotalStock());
        $this->assertEquals($airBefore - 400, $air->fresh()->getTotalStock());

        $adj = $result['adjustments'][0];
        $this->assertEquals('ingredient', $adj->adjustable_type);
        $this->assertEquals('decrease', $adj->adjustment_type);
        $this->assertEquals('expired', $adj->waste_category);
        $this->assertEquals(-2, (int) $adj->quantity);
        $this->assertEquals('Test waste', $adj->reason);
        $this->assertNotNull($adj->ingredient_id);
    }

    public function test_waste_insufficient_stock_throws_exception(): void
    {
        $category = Category::create(['name' => 'Minuman Test 2']);
        $menu = Menu::create([
            'category_id' => $category->id, 'name' => 'Kopi Insufficient Test',
            'description' => null, 'price' => 15000, 'image' => null,
            'is_available' => true, 'is_student_discount' => false, 'student_price' => null,
        ]);
        $gula = Ingredient::create(['name' => 'Gula Insufficient', 'unit' => 'gram', 'low_stock_threshold' => 10, 'is_active' => true]);
        IngredientBatch::create(['ingredient_id' => $gula->id, 'quantity' => 10, 'expiry_date' => now()->addDays(30), 'received_at' => now(), 'cost_per_unit' => 1]);
        MenuIngredient::create(['menu_id' => $menu->id, 'ingredient_id' => $gula->id, 'quantity_used' => 15]);
        $stockBefore = $gula->getTotalStock();
        $service = app(InventoryService::class);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stok tidak mencukupi');
        try { $service->wasteMenu(menuId: $menu->id, quantity: 1, wasteCategory: 'damaged', reason: 'Insufficient test', recordedBy: null); }
        catch (\RuntimeException $e) { $gula->refresh(); $this->assertEquals($stockBefore, $gula->getTotalStock()); throw $e; }
    }

    public function test_waste_invalid_category_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Kategori waste tidak valid');
        $service = app(InventoryService::class);
        $service->wasteMenu(menuId: 1, quantity: 1, wasteCategory: 'invalid_category', reason: 'Test', recordedBy: null);
    }

    public function test_waste_zero_quantity_throws_exception(): void
    {
        $category = Category::create(['name' => 'Minuman Test 3']);
        $menu = Menu::create([
            'category_id' => $category->id, 'name' => 'Test Zero',
            'description' => null, 'price' => 10000, 'image' => null,
            'is_available' => true, 'is_student_discount' => false, 'student_price' => null,
        ]);
        $service = app(InventoryService::class);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Jumlah waste harus lebih dari 0');
        $service->wasteMenu(menuId: $menu->id, quantity: 0, wasteCategory: 'expired', reason: 'Zero test', recordedBy: null);
    }
}
