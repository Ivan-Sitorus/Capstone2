<?php

namespace Tests\Feature\Order;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Cashier\CashierOrderController;
use App\Models\Order;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\OrderProcessingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        return Order::factory()->create(array_merge([
            'status' => 'pending',
            'payment_method' => 'cash',
            'total_amount' => 20000,
        ], $overrides));
    }

    /**
     * Simulate the lost update: this cashier read `pending`, but another
     * cashier already moved the row to `processing` before the conditional
     * update runs.
     */
    public function test_second_cashier_cannot_process_an_already_processed_order(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $order = $this->makeOrder();
        $stale = Order::findOrFail($order->id);

        DB::table('orders')->where('id', $order->id)->update(['status' => 'processing']);

        $request = Request::create('/kasir/pesanan/'.$order->id.'/status', 'PATCH', [
            'status' => 'processing',
        ]);

        $response = app(CashierOrderController::class)
            ->updateStatus($request, $stale, app(InventoryService::class));

        $this->assertSame(409, $response->getStatusCode());
        $this->assertSame(
            'Pesanan sudah diproses oleh kasir lain.',
            $response->getData(true)['message']
        );
        $this->assertSame('processing', $order->fresh()->status->value);
    }

    public function test_cancel_is_rejected_when_the_order_was_processed_in_the_meantime(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $order = $this->makeOrder();
        $stale = Order::findOrFail($order->id);

        DB::table('orders')->where('id', $order->id)->update(['status' => 'processing']);

        $request = Request::create('/kasir/pesanan/'.$order->id.'/cancel', 'PATCH', [
            'reason' => 'Salah pesan',
        ]);

        $response = app(CashierOrderController::class)->cancel($request, $stale);

        $this->assertSame(409, $response->getStatusCode());
        $this->assertSame(
            'Pesanan sudah diproses oleh kasir lain.',
            $response->getData(true)['message']
        );
        $this->assertSame('processing', $order->fresh()->status->value);
    }

    public function test_confirm_cash_cannot_process_an_order_twice(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $this->actingAs($cashier);

        $order = $this->makeOrder(['payment_method' => 'cash']);
        $stale = Order::findOrFail($order->id);

        DB::table('orders')->where('id', $order->id)->update(['status' => 'processing']);

        try {
            app(OrderProcessingService::class)->processOrder($stale, PaymentMethod::Cash);
            $this->fail('Pesanan yang sudah diproses seharusnya ditolak.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Pesanan sudah diproses oleh kasir lain.', $e->getMessage());
        }

        $this->assertSame('processing', $order->fresh()->status->value);
    }

    public function test_processing_transition_succeeds_when_no_one_raced(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $order = $this->makeOrder();

        $this->actingAs($cashier)
            ->patch(route('kasir.order.update-status', $order), ['status' => 'processing'])
            ->assertOk()
            ->assertJson(['message' => 'Status diperbarui.']);

        $this->assertSame('processing', $order->fresh()->status->value);
    }
}
