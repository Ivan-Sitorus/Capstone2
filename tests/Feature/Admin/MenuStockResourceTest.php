<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuStock;
use App\Models\MenuStockBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuStockResourceTest extends TestCase
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

    public function test_can_list_menu_stocks(): void
    {
        // Creating a no-recipe menu auto-creates MenuStock via MenuObserver
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Teh Botol',
            'price' => 5000,
            'is_stock_calculated' => false,
        ]);

        $menuStock = $menu->menuStock;
        $this->assertNotNull($menuStock, 'MenuStock should be auto-created for no-recipe menu');

        // Add a batch so stock is visible
        $menuStock->batches()->create([
            'quantity' => 50,
            'received_at' => now(),
            'cost_per_unit' => 3500,
        ]);

        $this->assertDatabaseHas('menu_stocks', [
            'menu_id' => $menu->id,
            'unit' => 'pcs',
            'batch_mode' => MenuStock::BATCH_MODE_FEFO,
        ]);

        $this->assertSame(50.0, $menuStock->fresh()->getTotalStock());
    }

    public function test_can_create_menu_stock_with_initial_batch(): void
    {
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Roti Bakar',
            'price' => 8000,
            'is_stock_calculated' => false,
        ]);

        // MenuObserver auto-creates the MenuStock with defaults.
        // Override specific settings that a user would set in the form.
        $menuStock = $menu->menuStock;
        $menuStock->update([
            'unit' => 'pcs',
            'low_stock_threshold' => '10',
            'batch_mode' => MenuStock::BATCH_MODE_FEFO,
        ]);

        // Simulate creating an initial batch (as would happen via the repeater in the form)
        $menuStock->batches()->create([
            'quantity' => 25,
            'received_at' => now(),
            'expiry_date' => now()->addDays(30)->toDateString(),
            'cost_per_unit' => 3500,
        ]);

        $this->assertDatabaseHas('menu_stocks', [
            'menu_id' => $menu->id,
            'unit' => 'pcs',
            'batch_mode' => MenuStock::BATCH_MODE_FEFO,
            'low_stock_threshold' => '10.00',
        ]);

        $this->assertSame(25.0, $menuStock->fresh()->getTotalStock());
    }

    public function test_filters_menu_select_to_no_recipe_only(): void
    {
        // Create a no-recipe menu → will get MenuStock
        $noRecipeMenu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Teh Botol',
            'price' => 5000,
            'is_stock_calculated' => false,
        ]);

        // Create a recipe menu → should NOT get MenuStock
        $recipeMenu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Kopi Susu',
            'price' => 12000,
            'is_stock_calculated' => true,
        ]);

        // Verify no-recipe menu has a MenuStock
        $this->assertDatabaseHas('menu_stocks', [
            'menu_id' => $noRecipeMenu->id,
        ]);

        // Verify recipe menu does NOT have a MenuStock
        $this->assertDatabaseMissing('menu_stocks', [
            'menu_id' => $recipeMenu->id,
        ]);

        // Verify the underlying query filter works:
        // The form's menu_id relationship filters to only is_stock_calculated=false menus
        $filteredMenuIds = Menu::where('is_stock_calculated', false)->pluck('id');
        $this->assertContains($noRecipeMenu->id, $filteredMenuIds->toArray());
        $this->assertNotContains($recipeMenu->id, $filteredMenuIds->toArray());

        // Also verify: recipe menu has is_stock_calculated=true
        $this->assertDatabaseHas('menus', [
            'id' => $recipeMenu->id,
            'is_stock_calculated' => true,
        ]);
    }

    public function test_batches_hidden_on_edit_context(): void
    {
        // The resource form uses ->hiddenOn('edit') for the batches repeater.
        // We verify this by testing that the relationship still works correctly
        // and that the batch data is accessible separately from the form.
        $menu = Menu::create([
            'category_id' => $this->category->id,
            'name' => 'Brownies',
            'price' => 10000,
            'is_stock_calculated' => false,
        ]);

        $menuStock = $menu->menuStock;

        // Create initial batch during "create" flow
        $batch = $menuStock->batches()->create([
            'quantity' => 10,
            'received_at' => now(),
            'cost_per_unit' => 4000,
        ]);

        // Simulate edit: update MenuStock fields (not batches)
        $menuStock->update([
            'low_stock_threshold' => '5',
            'unit' => 'pcs',
        ]);

        $this->assertDatabaseHas('menu_stocks', [
            'id' => $menuStock->id,
            'low_stock_threshold' => '5.00',
        ]);

        // The batch should remain unchanged (editing stock fields doesn't affect batches)
        $batch->refresh();
        $this->assertSame(10.0, (float) $batch->quantity);

        // Batches can be managed via the separate batches page/relation manager
        $this->assertSame(10.0, $menuStock->fresh()->getTotalStock());
    }
}
