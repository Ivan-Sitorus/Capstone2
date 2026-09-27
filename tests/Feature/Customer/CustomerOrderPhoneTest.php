<?php

namespace Tests\Feature\Customer;

use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderPhoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_phone_alias_is_persisted_and_visible_in_history(): void
    {
        $category = Category::create(['name' => 'Kopi']);
        $menu = Menu::create([
            'category_id' => $category->id,
            'name'        => 'Kopi Susu',
            'price'       => 16000,
            'status'      => 'active',
        ]);
        $table = CafeTable::create(['table_number' => 9]);

        $response = $this->postJson(route('customer.order.store'), [
            'customer_name'  => 'Budi',
            'customer_phone' => '081234567890',
            'table_id'       => $table->id,
            'items'          => [
                ['menu_id' => $menu->id, 'quantity' => 2],
            ],
        ]);

        $response->assertCreated();

        $order = Order::latest('id')->first();
        $this->assertSame('081234567890', $order->phone, 'Nomor telepon pelanggan harus tersimpan.');

        // Kondisi setelah pelanggan memilih QRIS dan kasir mengonfirmasi.
        $order->update(['payment_method' => 'qris', 'status' => 'processing']);

        $this->get(route('customer.history', ['phone' => '081234567890']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Customer/History/Index', false)
                ->has('orders', 1));
    }
}
