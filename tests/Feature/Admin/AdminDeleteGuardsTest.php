<?php

namespace Tests\Feature\Admin;

use App\Enums\MenuStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Filament\Resources\CafeTableResource\Pages\ListCafeTables;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Filament\Resources\StockResource\Pages\ListStocks;
use App\Filament\Resources\StockResource\Pages\ManageBatches;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\BatchPayment;
use App\Models\CafeTable;
use App\Models\CashierHistory;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\Order;
use App\Models\StockMovement;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Guards every admin-deletable record so a delete either succeeds cleanly or is
 * refused with a Bahasa Indonesia notification and no side effects.
 */
class AdminDeleteGuardsTest extends TestCase
{
    use RefreshDatabase;

    private function asAdmin(): User
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin->value,
            'status' => UserStatus::Active->value,
        ]);

        $this->actingAs($admin, 'admin');
        Filament::setCurrentPanel('admin');

        return $admin;
    }

    private function deleteAction(object $record): TestAction
    {
        return TestAction::make(DeleteAction::class)->table($record);
    }

    private function makeMenu(Category $category, string $name = 'Kopi Susu'): Menu
    {
        return Menu::create([
            'category_id' => $category->id,
            'name' => $name,
            'price' => 12000,
            'cost_price' => 7000,
            'status' => MenuStatus::Active->value,
        ]);
    }

    private function makeIngredient(): Ingredient
    {
        return Ingredient::factory()->create(['batch_mode' => 'fefo']);
    }

    private function makeBatch(Ingredient $ingredient, float $quantity = 100): IngredientBatch
    {
        return IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => $quantity,
            'initial_quantity' => $quantity,
            'total_cost' => 50000,
            'payment_status' => 'unpaid',
            'received_at' => now(),
        ]);
    }

    // ------------------------------------------------------------------ Category

    public function test_category_with_menus_cannot_be_deleted(): void
    {
        $this->asAdmin();
        $category = Category::create(['name' => 'Kopi']);
        $this->makeMenu($category);

        Livewire::test(ListCategories::class)
            ->callAction($this->deleteAction($category))
            ->assertActionHalted($this->deleteAction($category))
            ->assertNotified('Kategori tidak dapat dihapus');

        $this->assertDatabaseHas('menu_categories', [
            'id' => $category->id,
            'deleted_at' => null,
        ]);
    }

    public function test_category_without_menus_can_be_deleted(): void
    {
        $this->asAdmin();
        $category = Category::create(['name' => 'Teh']);

        Livewire::test(ListCategories::class)
            ->callAction($this->deleteAction($category));

        $this->assertSoftDeleted('menu_categories', ['id' => $category->id]);
    }

    // ---------------------------------------------------------------- Ingredient

    public function test_ingredient_used_by_a_recipe_cannot_be_deleted(): void
    {
        $this->asAdmin();
        $menu = $this->makeMenu(Category::create(['name' => 'Kopi']));
        $ingredient = $this->makeIngredient();

        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 10,
        ]);

        Livewire::test(ListStocks::class)
            ->callAction($this->deleteAction($ingredient))
            ->assertActionHalted($this->deleteAction($ingredient))
            ->assertNotified("Bahan baku '{$ingredient->name}' tidak dapat dihapus");

        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id,
            'deleted_at' => null,
        ]);
    }

    public function test_ingredient_referenced_only_by_a_soft_deleted_menu_is_still_blocked(): void
    {
        $this->asAdmin();
        $menu = $this->makeMenu(Category::create(['name' => 'Kopi']));
        $ingredient = $this->makeIngredient();

        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 10,
        ]);

        $menu->delete();

        Livewire::test(ListStocks::class)
            ->callAction($this->deleteAction($ingredient))
            ->assertActionHalted($this->deleteAction($ingredient));

        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id,
            'deleted_at' => null,
        ]);
    }

    public function test_ingredient_without_recipe_can_be_deleted(): void
    {
        $this->asAdmin();
        $ingredient = $this->makeIngredient();

        Livewire::test(ListStocks::class)
            ->callAction($this->deleteAction($ingredient));

        $this->assertSoftDeleted('ingredients', ['id' => $ingredient->id]);
    }

    // ----------------------------------------------------------------- CafeTable

    public function test_cafe_table_with_pending_order_cannot_be_deleted(): void
    {
        $this->asAdmin();
        $table = CafeTable::create(['table_number' => 1]);

        Order::factory()->create([
            'table_id' => $table->id,
            'status' => OrderStatus::Pending->value,
        ]);

        Livewire::test(ListCafeTables::class)
            ->callAction($this->deleteAction($table))
            ->assertActionHalted($this->deleteAction($table))
            ->assertNotified('Meja tidak dapat dihapus');

        $this->assertDatabaseHas('cafe_tables', ['id' => $table->id, 'deleted_at' => null]);
    }

    public function test_cafe_table_with_unpaid_order_cannot_be_deleted(): void
    {
        $this->asAdmin();
        $table = CafeTable::create(['table_number' => 2]);

        Order::factory()->payLater()->create([
            'table_id' => $table->id,
            'status' => OrderStatus::Unpaid->value,
        ]);

        Livewire::test(ListCafeTables::class)
            ->callAction($this->deleteAction($table))
            ->assertActionHalted($this->deleteAction($table))
            ->assertNotified('Meja tidak dapat dihapus');

        $this->assertDatabaseHas('cafe_tables', ['id' => $table->id, 'deleted_at' => null]);
    }

    public function test_cafe_table_with_only_finished_orders_can_be_deleted(): void
    {
        $this->asAdmin();
        $table = CafeTable::create(['table_number' => 3]);

        Order::factory()->completed()->create(['table_id' => $table->id]);
        Order::factory()->create([
            'table_id' => $table->id,
            'status' => OrderStatus::Cancelled->value,
        ]);

        Livewire::test(ListCafeTables::class)
            ->callAction($this->deleteAction($table));

        $this->assertSoftDeleted('cafe_tables', ['id' => $table->id]);
    }

    // ----------------------------------------------------------- IngredientBatch

    public function test_ingredient_batch_with_stock_history_cannot_be_deleted(): void
    {
        $this->asAdmin();
        $ingredient = $this->makeIngredient();
        $batch = $this->makeBatch($ingredient, 100);

        StockMovement::create([
            'ingredient_id' => $ingredient->id,
            'ingredient_batch_id' => $batch->id,
            'movement_type' => 'purchase',
            'quantity_before' => 0,
            'quantity_change' => 100,
            'quantity_after' => 100,
        ]);

        Livewire::test(ManageBatches::class, ['record' => $ingredient])
            ->callAction($this->deleteAction($batch))
            ->assertActionHalted($this->deleteAction($batch))
            ->assertNotified('Batch tidak dapat dihapus');

        // The guard must not silently zero the remaining stock.
        $this->assertDatabaseHas('ingredient_batches', [
            'id' => $batch->id,
            'deleted_at' => null,
        ]);
        $this->assertSame(100.0, (float) IngredientBatch::find($batch->id)->quantity);
    }

    public function test_ingredient_batch_with_payment_history_cannot_be_deleted(): void
    {
        $this->asAdmin();
        $ingredient = $this->makeIngredient();
        $batch = $this->makeBatch($ingredient, 50);

        BatchPayment::create([
            'ingredient_batch_id' => $batch->id,
            'amount' => 50000,
            'payment_date' => now(),
            'payment_method' => 'cash',
        ]);

        Livewire::test(ManageBatches::class, ['record' => $ingredient])
            ->callAction($this->deleteAction($batch))
            ->assertActionHalted($this->deleteAction($batch))
            ->assertNotified('Batch tidak dapat dihapus');

        $this->assertDatabaseHas('ingredient_batches', [
            'id' => $batch->id,
            'deleted_at' => null,
        ]);
    }

    public function test_ingredient_batch_without_history_can_be_deleted(): void
    {
        $this->asAdmin();
        $ingredient = $this->makeIngredient();
        $batch = $this->makeBatch($ingredient, 25);

        Livewire::test(ManageBatches::class, ['record' => $ingredient])
            ->callAction($this->deleteAction($batch));

        $this->assertSoftDeleted('ingredient_batches', ['id' => $batch->id]);
    }

    // ---------------------------------------------------------------------- User

    public function test_user_with_cashier_history_can_be_deleted_and_history_is_preserved(): void
    {
        $this->asAdmin();

        $cashier = User::factory()->create([
            'role' => UserRole::Cashier->value,
            'status' => UserStatus::Active->value,
        ]);

        $history = CashierHistory::create([
            'user_id' => $cashier->id,
            'session_id' => 'sess-guard-test',
            'started_at' => now()->subHour(),
            'last_activity_at' => now(),
            'is_active' => false,
        ]);

        Livewire::test(ListUsers::class)
            ->callAction($this->deleteAction($cashier));

        $this->assertDatabaseMissing('users', ['id' => $cashier->id]);
        $this->assertDatabaseHas('cashier_histories', [
            'id' => $history->id,
            'user_id' => null,
        ]);
    }
}
