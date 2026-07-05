<?php

namespace Tests\Unit\Inventory;

use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class IngredientModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_total_stock_sums_all_batches(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Kopi Bubuk Test',
            'unit' => 'gram',
            'low_stock_threshold' => 100,
            'is_active' => true,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 120,
            'expiry_date' => now()->addDays(7)->toDateString(),
            'received_at' => now()->subDays(2),
            'cost_per_unit' => 2,
        ]);

        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 80,
            'expiry_date' => now()->addDays(14)->toDateString(),
            'received_at' => now()->subDay(),
            'cost_per_unit' => 2,
        ]);

        $this->assertSame(200.0, $ingredient->getTotalStock());
    }

    public function test_stock_movement_is_immutable_on_update(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Test',
            'unit' => 'gram',
        ]);

        $movement = StockMovement::create([
            'ingredient_id' => $ingredient->id,
            'movement_type' => 'purchase',
            'quantity_before' => 0,
            'quantity_change' => 100,
            'quantity_after' => 100,
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Stock movements are immutable');

        $movement->update(['notes' => 'should fail']);
    }

    public function test_stock_movement_is_immutable_on_delete(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Test',
            'unit' => 'gram',
        ]);

        $movement = StockMovement::create([
            'ingredient_id' => $ingredient->id,
            'movement_type' => 'purchase',
            'quantity_before' => 0,
            'quantity_change' => 100,
            'quantity_after' => 100,
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Stock movements are immutable');

        $movement->delete();
    }
}
