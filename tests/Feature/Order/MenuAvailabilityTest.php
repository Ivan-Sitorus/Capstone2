<?php

namespace Tests\Feature\Order;

use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private function makeMenu(string $status): Menu
    {
        $category = Category::create(['name' => 'Kopi']);

        return Menu::create([
            'category_id' => $category->id,
            'name' => 'Kopi Susu',
            'price' => 12000,
            'cost_price' => 7000,
            'status' => $status,
        ]);
    }

    public function test_cashier_cannot_order_a_deactivated_menu(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $menu = $this->makeMenu('inactive');

        $response = $this->actingAs($cashier)
            ->from(route('kasir.new-order'))
            ->post(route('kasir.new-order.store'), [
                'payment_method' => 'cash',
                'items' => [['menu_id' => $menu->id, 'quantity' => 1]],
            ]);

        $response->assertRedirect(route('kasir.new-order'));
        $response->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_customer_cannot_order_a_deactivated_menu(): void
    {
        $table = CafeTable::create(['table_number' => 1]);
        $menu = $this->makeMenu('inactive');

        $response = $this->postJson(route('customer.order.store'), [
            'customer_name' => 'Budi',
            'table_id' => $table->id,
            'items' => [['menu_id' => $menu->id, 'quantity' => 1]],
        ]);

        $response->assertStatus(409);
        $response->assertJson(['message' => 'Menu Kopi Susu tidak tersedia.']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_cashier_can_order_an_active_menu(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $menu = $this->makeMenu('active');

        $response = $this->actingAs($cashier)
            ->from(route('kasir.new-order'))
            ->post(route('kasir.new-order.store'), [
                'payment_method' => 'cash',
                'items' => [['menu_id' => $menu->id, 'quantity' => 2]],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseHas('order_items', [
            'menu_id' => $menu->id,
            'quantity' => 2,
        ]);
    }
}
