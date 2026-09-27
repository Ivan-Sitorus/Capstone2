<?php

namespace Tests\Feature\Admin;

use App\Enums\MenuStatus;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Locks down the actual ON DELETE behaviour of the schema so the admin guards
 * stay the single source of truth and no unexpected cascade surprises appear.
 */
class DeleteForeignKeyBehaviorTest extends TestCase
{
    use RefreshDatabase;

    private function makeMenu(): Menu
    {
        $category = Category::create(['name' => 'Kopi']);

        return Menu::create([
            'category_id' => $category->id,
            'name' => 'Kopi Susu',
            'price' => 12000,
            'cost_price' => 7000,
            'status' => MenuStatus::Active->value,
        ]);
    }

    public function test_soft_deleting_ingredient_keeps_menu_ingredient_pivot(): void
    {
        $menu = $this->makeMenu();
        $ingredient = Ingredient::factory()->create(['batch_mode' => 'fefo']);

        $pivot = MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 10,
        ]);

        $ingredient->delete();

        $this->assertSoftDeleted('ingredients', ['id' => $ingredient->id]);
        $this->assertDatabaseHas('menu_ingredients', [
            'id' => $pivot->id,
            'ingredient_id' => $ingredient->id,
        ]);
    }

    public function test_hard_deleting_ingredient_sets_menu_ingredient_ingredient_id_null(): void
    {
        $menu = $this->makeMenu();
        $ingredient = Ingredient::factory()->create(['batch_mode' => 'fefo']);

        $pivot = MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 10,
        ]);

        $ingredient->forceDelete();

        $this->assertDatabaseHas('menu_ingredients', [
            'id' => $pivot->id,
            'menu_id' => $menu->id,
            'ingredient_id' => null,
        ]);
    }

    public function test_hard_deleting_ingredient_cascades_stock_movements(): void
    {
        $ingredient = Ingredient::factory()->create(['batch_mode' => 'fefo']);

        $movement = StockMovement::create([
            'ingredient_id' => $ingredient->id,
            'movement_type' => 'purchase',
            'quantity_before' => 0,
            'quantity_change' => 10,
            'quantity_after' => 10,
        ]);

        $ingredient->forceDelete();

        $this->assertDatabaseMissing('stock_movements', ['id' => $movement->id]);
    }

    public function test_hard_deleting_order_cascades_order_items_and_payments(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $menu = $this->makeMenu();

        $order = Order::factory()->create([
            'cashier_id' => $cashier->id,
            'status' => 'completed',
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'quantity' => 2,
            'unit_price' => 12000,
            'subtotal' => 24000,
        ]);

        $payment = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 24000,
            'payment_date' => now(),
            'payment_method' => 'cash',
        ]);

        $order->delete();

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('order_items', ['id' => $item->id]);
        $this->assertDatabaseMissing('order_payments', ['id' => $payment->id]);
    }
}
