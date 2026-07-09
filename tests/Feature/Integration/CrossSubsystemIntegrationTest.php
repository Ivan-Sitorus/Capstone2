<?php

namespace Tests\Feature\Integration;

use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CrossSubsystemIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_order_deducts_stock_and_creates_movement(): void
    {
        Cache::flush();
        $cashier = User::factory()->create(['role' => 'cashier']);
        $category = Category::create(['name' => 'Minuman']);
        $menu = Menu::create([
            'name' => 'Kopi Susu',
            'price' => 12000,
            'category_id' => $category->id,
        ]);
        $ingredient = Ingredient::create([
            'name' => 'Kopi Bubuk',
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
        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 10,
        ]);

        $this->actingAs($cashier);
        $response = $this->post(route('kasir.pesanan-baru.simpan'), [
            'items' => [['menu_id' => $menu->id, 'quantity' => 2]],
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHas('success');

        $this->assertSame(80.0, (float) $batch->fresh()->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'ingredient_id' => $ingredient->id,
            'quantity_before' => 100,
            'quantity_change' => -20,
            'quantity_after' => 80,
        ]);
        $this->assertDatabaseHas('daily_ingredient_usages', [
            'ingredient_id' => $ingredient->id,
        ]);
    }

    public function test_cashier_order_fails_when_stock_insufficient(): void
    {
        Cache::flush();
        $cashier = User::factory()->create(['role' => 'cashier']);
        $category = Category::create(['name' => 'Minuman']);
        $menu = Menu::create([
            'name' => 'Kopi Susu',
            'price' => 12000,
            'category_id' => $category->id,
        ]);
        $ingredient = Ingredient::create([
            'name' => 'Kopi Bubuk',
            'unit' => 'gram',
            'low_stock_threshold' => 10,
        ]);
        IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 15,
            'expiry_date' => now()->addDays(30),
            'received_at' => now(),
            'cost_per_unit' => 1000,
        ]);
        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 10,
        ]);

        $this->actingAs($cashier);
        $response = $this->from('/kasir/pesanan-baru')->post(route('kasir.pesanan-baru.simpan'), [
            'items' => [['menu_id' => $menu->id, 'quantity' => 2]],
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_customer_order_flow_to_cashier_confirmation_deducts_stock(): void
    {
        Cache::flush();
        $customer = User::factory()->create(['role' => 'customer']);
        $cashier = User::factory()->create(['role' => 'cashier']);
        $category = Category::create(['name' => 'Minuman']);
        $menu = Menu::create([
            'name' => 'Es Kopi',
            'price' => 15000,
            'category_id' => $category->id,
            'is_available' => true,
        ]);
        $ingredient = Ingredient::create([
            'name' => 'Kopi Bubuk',
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
        MenuIngredient::create([
            'menu_id' => $menu->id,
            'ingredient_id' => $ingredient->id,
            'quantity_used' => 10,
        ]);
        $table = CafeTable::create([
            'table_number' => 1,
            'qr_code' => 'table-1',
        ]);

        $this->actingAs($customer);
        $response = $this->postJson(route('customer.order.store'), [
            'customer_name' => 'Budi',
            'customer_phone' => '081234567890',
            'table_id' => $table->id,
            'items' => [['menu_id' => $menu->id, 'quantity' => 2]],
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['order_code', 'total_amount', 'order_id']);

        $orderId = $response->json('order_id');
        $order = Order::find($orderId);
        $this->assertNotNull($order);
        $this->assertEquals('Budi', $order->customer_name);

        $this->assertSame(100.0, (float) $batch->fresh()->quantity);

        $this->actingAs($cashier);
        $order->update(['payment_method' => 'cash']);
        $confirmResponse = $this->patch(route('kasir.pesanan.konfirmasi-tunai', ['order' => $orderId]));

        $confirmResponse->assertStatus(200);
        $this->assertSame(80.0, (float) $batch->fresh()->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'ingredient_id' => $ingredient->id,
            'quantity_change' => -20,
        ]);
    }
}
