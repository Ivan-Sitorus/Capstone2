<?php

namespace Tests\Feature\Admin\Dashboard;

use App\Models\IngredientBatch;
use App\Models\User;
use Database\Factories\IngredientFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_active_ingredients_count(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        IngredientFactory::new()->count(5)->create(['is_active' => true]);
        IngredientFactory::new()->count(3)->create(['is_active' => false]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\InventoryOverview::class)
            ->assertSee('5');
    }

    public function test_low_stock_count(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $lowThreshold = 10;

        $lowStockIngredients = IngredientFactory::new()->count(3)->create([
            'is_active' => true,
            'low_stock_threshold' => $lowThreshold,
        ]);

        foreach ($lowStockIngredients as $ingredient) {
            IngredientBatch::create([
                'ingredient_id' => $ingredient->id,
                'quantity' => $lowThreshold - 5,
            ]);
        }

        $normalIngredients = IngredientFactory::new()->count(2)->create([
            'is_active' => true,
            'low_stock_threshold' => $lowThreshold,
        ]);

        foreach ($normalIngredients as $ingredient) {
            IngredientBatch::create([
                'ingredient_id' => $ingredient->id,
                'quantity' => $lowThreshold + 20,
            ]);
        }

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\InventoryOverview::class)
            ->assertSee('3');
    }

    public function test_zero_ingredients_shows_zero(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\InventoryOverview::class)
            ->assertSee('0');
    }

    public function test_default_period_null(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        IngredientFactory::new()->count(2)->create(['is_active' => true]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\InventoryOverview::class)
            ->assertSee('Total');
    }
}
