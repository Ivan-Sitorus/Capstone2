<?php

namespace Tests\Unit\Inventory;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Services\InventoryService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceSkipValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_behavior_throws_on_insufficient_stock(): void
    {
        $category = Category::create([
            'name' => 'Kategori Test ' . uniqid(),
            'slug' => 'kategori-test-' . uniqid(),
            'is_active' => true,
        ]);

        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Menu Test',
            'slug' => 'menu-test-' . uniqid(),
            'description' => null,
            'price' => 10000,
            'cashback' => 0,
            'image' => null,
            'is_available' => true,
            'is_student_discount' => false,
            'student_price' => null,
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Bahan Test',
            'unit' => 'gram',
            'low_stock_threshold' => 10,
            'is_active' => true,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 5,
            'expiry_date' => now()->addDays(10)->toDateString(),
            'received_at' => now(),
            'cost_per_unit' => 1,
        ]);

        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 15,
        ]);

        $service = app(InventoryService::class);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Stok tidak mencukupi');

        $service->decreaseStockForOrder([
            ['menu_id' => $menu->id, 'quantity' => 2],
        ]);
    }
}
