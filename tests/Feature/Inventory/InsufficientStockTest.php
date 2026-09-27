<?php

namespace Tests\Feature\Inventory;

use App\Exceptions\InsufficientStockException;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InsufficientStockTest extends TestCase
{
    use RefreshDatabase;

    private function makeShortStockOrder(): Order
    {
        $ingredient = Ingredient::factory()->create(['unit' => 'gram', 'batch_mode' => 'fefo']);
        IngredientBatch::factory()->create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 1,
            'expiry_date' => null,
            'payment_status' => 'unpaid',
        ]);

        $category = Category::create(['name' => 'Kopi']);
        $menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Kopi Susu',
            'price' => 12000,
            'cost_price' => 7000,
            'status' => 'active',
        ]);

        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 5,
            'unit' => 'gram',
        ]);

        $order = Order::factory()->create([
            'status' => 'pending',
            'payment_method' => 'cash',
            'total_amount' => 12000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'quantity' => 1,
            'unit_price' => 12000,
            'cost_price' => 7000,
            'subtotal' => 12000,
        ]);

        return $order;
    }

    public function test_inventory_service_throws_a_dedicated_insufficient_stock_exception(): void
    {
        $order = $this->makeShortStockOrder();
        $item = $order->items()->firstOrFail();

        $this->expectException(InsufficientStockException::class);

        app(InventoryService::class)->decreaseStockForOrder([[
            'menu_id' => $item->menu_id,
            'quantity' => 1,
            'order_id' => $order->id,
            'order_item_id' => $item->id,
        ]]);
    }

    public function test_status_update_surfaces_insufficient_stock_as_409(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $order = $this->makeShortStockOrder();

        $response = $this->actingAs($cashier)
            ->patch(route('kasir.order.update-status', $order), ['status' => 'processing']);

        $response->assertStatus(409);
        $this->assertStringContainsString('tidak mencukupi', $response->json('message'));
        $this->assertSame('pending', $order->fresh()->status->value);
    }
}
