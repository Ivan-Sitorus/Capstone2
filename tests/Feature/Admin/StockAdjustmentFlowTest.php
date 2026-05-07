<?php

namespace Tests\Feature\Admin;

use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\User;
use App\Services\StockReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAdjustmentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_increase_adjustment_stores_positive_quantity_and_updates_stock(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Syrup Increase Test',
            'unit' => 'ml',
            'low_stock_threshold' => 100,
            'is_active' => true,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 40,
            'expiry_date' => now()->addDays(15)->toDateString(),
            'received_at' => now()->subDays(4),
            'cost_per_unit' => 2,
        ]);

        $latestBatch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 30,
            'expiry_date' => now()->addDays(20)->toDateString(),
            'received_at' => now()->subDay(),
            'cost_per_unit' => 2,
        ]);

        $service = app(StockReconciliationService::class);

        $adjustment = $service->createManualAdjustment(
            ingredientId: $ingredient->id,
            quantity: 20,
            adjustmentType: 'increase',
            reason: 'Restock correction',
            reportedBy: $admin->id,
        );

        $latestBatch->refresh();

        $this->assertSame(50.0, (float) $latestBatch->quantity);
        $this->assertSame(20.0, (float) $adjustment->quantity);
        $this->assertSame('increase', $adjustment->adjustment_type);
        $this->assertSame(70.0, (float) $adjustment->quantity_before);
        $this->assertSame(90.0, (float) $adjustment->quantity_after);

        $this->assertDatabaseHas('stock_movements', [
            'stock_adjustment_id' => $adjustment->id,
            'movement_type' => 'adjustment_increase',
            'recorded_by' => $admin->id,
        ]);
    }

    public function test_decrease_adjustment_stores_negative_quantity_and_reduces_stock(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $ingredient = Ingredient::create([
            'name' => 'Coffee Decrease Test',
            'unit' => 'gram',
            'low_stock_threshold' => 50,
            'is_active' => true,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 60,
            'expiry_date' => now()->addDays(5)->toDateString(),
            'received_at' => now()->subDays(3),
            'cost_per_unit' => 3,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 40,
            'expiry_date' => now()->addDays(15)->toDateString(),
            'received_at' => now()->subDay(),
            'cost_per_unit' => 3,
        ]);

        $service = app(StockReconciliationService::class);

        $adjustment = $service->createManualAdjustment(
            ingredientId: $ingredient->id,
            quantity: 25,
            adjustmentType: 'decrease',
            reason: 'Physical count mismatch',
            reportedBy: $admin->id,
        );

        $ingredient->refresh();

        $this->assertSame(-25.0, (float) $adjustment->quantity);
        $this->assertSame('decrease', $adjustment->adjustment_type);
        $this->assertSame(100.0, (float) $adjustment->quantity_before);
        $this->assertSame(75.0, (float) $adjustment->quantity_after);
        $this->assertSame(75.0, (float) $ingredient->getTotalStock());

        $this->assertDatabaseHas('stock_movements', [
            'stock_adjustment_id' => $adjustment->id,
            'movement_type' => 'adjustment_decrease',
            'recorded_by' => $admin->id,
        ]);
    }

    public function test_non_admin_cannot_access_stock_adjustment_admin_resource(): void
    {
        /** @var User $cashier */
        $cashier = User::factory()->create([
            'role' => 'cashier',
        ]);

        $response = $this->actingAs($cashier)
            ->get(route('filament.admin.resources.stock-adjustments.index'));

        $this->assertNotSame(200, $response->getStatusCode());
    }

    public function test_decrease_adjustment_rejects_negative_quantity_input(): void
    {
        $this->expectException(\RuntimeException::class);

        $service = app(StockReconciliationService::class);

        $service->createManualAdjustment(
            ingredientId: 1,
            quantity: -10,
            adjustmentType: 'decrease',
            reason: 'Test negative input',
        );
    }
}
