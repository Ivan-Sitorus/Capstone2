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
        ]);

        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Menu Test',
            'price' => 10000,
            'status' => 'active',
            'image' => null,
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Bahan Test',
            'unit' => 'gram', 'batch_mode' => 'fefo',
            'low_stock_threshold' => 10,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 5,
            'expiry_date' => now()->addDays(10)->toDateString(),
            'received_at' => now(),
            'total_cost' => 1000,
            'payment_status' => 'paid',
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
