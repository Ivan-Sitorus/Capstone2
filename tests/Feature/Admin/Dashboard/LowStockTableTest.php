<?php

namespace Tests\Feature\Admin\Dashboard;

use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LowStockTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_low_stock_ingredients_appear(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // 3 low-stock ingredients (stock <= threshold)
        $low1 = Ingredient::create(['name' => 'Kopi Low Stock', 'unit' => 'gram', 'low_stock_threshold' => 10, 'is_active' => true]);
        IngredientBatch::create(['ingredient_id' => $low1->id, 'quantity' => 3]);

        $low2 = Ingredient::create(['name' => 'Gula Low Stock', 'unit' => 'kg', 'low_stock_threshold' => 20, 'is_active' => true]);
        IngredientBatch::create(['ingredient_id' => $low2->id, 'quantity' => 10]);

        $low3 = Ingredient::create(['name' => 'Susu At Threshold', 'unit' => 'liter', 'low_stock_threshold' => 15, 'is_active' => true]);
        IngredientBatch::create(['ingredient_id' => $low3->id, 'quantity' => 15]); // exactly at threshold

        // 2 normal-stock ingredients (stock > threshold)
        $normal1 = Ingredient::create(['name' => 'Teh Normal', 'unit' => 'sachet', 'low_stock_threshold' => 10, 'is_active' => true]);
        IngredientBatch::create(['ingredient_id' => $normal1->id, 'quantity' => 50]);

        $normal2 = Ingredient::create(['name' => 'Mie Normal', 'unit' => 'pcs', 'low_stock_threshold' => 20, 'is_active' => true]);
        IngredientBatch::create(['ingredient_id' => $normal2->id, 'quantity' => 100]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\LowStockTable::class)
            ->assertSee($low1->name)
            ->assertSee($low2->name)
            ->assertSee($low3->name)
            ->assertDontSee($normal1->name)
            ->assertDontSee($normal2->name);
    }

    public function test_threshold_boundary_included(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $ingredient = Ingredient::create([
            'name' => 'Gula Pasir',
            'unit' => 'kg',
            'low_stock_threshold' => 10,
            'is_active' => true,
        ]);
        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 10, // exactly equal to threshold
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\LowStockTable::class)
            ->assertSee($ingredient->name);
    }

    public function test_inactive_ingredients_excluded(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $inactive = Ingredient::create([
            'name' => 'Inactive Item',
            'unit' => 'pcs',
            'low_stock_threshold' => 10,
            'is_active' => false,
        ]);
        IngredientBatch::create([
            'ingredient_id' => $inactive->id,
            'quantity' => 5,
        ]);

        $active = Ingredient::create([
            'name' => 'Active Item',
            'unit' => 'pcs',
            'low_stock_threshold' => 10,
            'is_active' => true,
        ]);
        IngredientBatch::create([
            'ingredient_id' => $active->id,
            'quantity' => 3,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\LowStockTable::class)
            ->assertSee($active->name)
            ->assertDontSee($inactive->name);
    }

    public function test_zero_low_stock_shows_empty(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // All ingredients above threshold
        $ingredient = Ingredient::create([
            'name' => 'Stok Melimpah',
            'unit' => 'kg',
            'low_stock_threshold' => 10,
            'is_active' => true,
        ]);
        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 100,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\LowStockTable::class)
            ->assertSuccessful();
    }

    public function test_table_columns_present(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $ingredient = Ingredient::create([
            'name' => 'Mentega',
            'unit' => 'gram',
            'low_stock_threshold' => 5,
            'is_active' => true,
        ]);
        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 2,
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\LowStockTable::class)
            ->assertSee($ingredient->name)
            ->assertSee('Low Stock Threshold')
            ->assertSee('Unit');
    }
}
