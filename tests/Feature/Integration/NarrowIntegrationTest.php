<?php

namespace Tests\Feature\Integration;

use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\Category;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\StockReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NarrowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_addition_does_not_create_stock_movement(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Test Bahan',
            'unit' => 'gram',
            'low_stock_threshold' => 10,
        ]);
        $batch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 50,
            'expiry_date' => now()->addDays(30),
            'received_at' => now(),
            'cost_per_unit' => 1000,
        ]);

        $this->assertDatabaseHas('ingredient_batches', [
            'id' => $batch->id,
            'quantity' => 50,
        ]);

        $this->assertDatabaseMissing('stock_movements', [
            'ingredient_id' => $ingredient->id,
        ]);
    }

    public function test_adjustment_increase_and_reversal_restores_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $ingredient = Ingredient::create([
            'name' => 'Test Bahan',
            'unit' => 'gram',
            'low_stock_threshold' => 10,
        ]);
        $batch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 100,
            'expiry_date' => now()->addDays(30),
            'received_at' => now(),
            'cost_per_unit' => 1000,
        ]);

        $adjustment = app(StockReconciliationService::class)
            ->createManualAdjustment(
                adjustableType: 'ingredient',
                ingredientId: $ingredient->id,
                quantity: 30,
                adjustmentType: 'increase',
                reason: 'Restock',
                reportedBy: $admin->id,
            );

        $this->assertSame(130.0, (float) $batch->fresh()->quantity);

        $adjustment->update(['status' => 'cancelled', 'cancel_reason' => 'Salah input']);
        $batch->decrement('quantity', 30);

        $this->assertSame(100.0, (float) $batch->fresh()->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'ingredient_id' => $ingredient->id,
            'stock_adjustment_id' => $adjustment->id,
        ]);
    }

    public function test_fefo_deducts_nearest_expiry_first(): void
    {
        $category = Category::create(['name' => 'Minuman']);
        $menu = Menu::create([
            'name' => 'Test Menu FEFO',
            'price' => 10000,
            'category_id' => $category->id,
        ]);
        $ingredient = Ingredient::create([
            'name' => 'Test Bahan',
            'unit' => 'gram',
            'low_stock_threshold' => 10,
        ]);
        $nearExpiry = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 80,
            'expiry_date' => now()->addDays(3),
            'received_at' => now()->subDays(5),
            'cost_per_unit' => 1000,
        ]);
        $farExpiry = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 80,
            'expiry_date' => now()->addDays(30),
            'received_at' => now()->subDays(1),
            'cost_per_unit' => 1000,
        ]);
        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 50,
        ]);

        app(InventoryService::class)->decreaseStockForOrder([
            ['menu_id' => $menu->id, 'quantity' => 2],
        ]);

        $this->assertSame(0.0, (float) $nearExpiry->fresh()->quantity);
        $this->assertSame(60.0, (float) $farExpiry->fresh()->quantity);
    }
}
