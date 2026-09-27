<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Filament\Resources\ReceivableResource\Pages\RiwayatBayar;
use App\Filament\Resources\StockResource\Pages\RiwayatBayarBatch;
use App\Models\BatchPayment;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Payment rows are intentionally deletable: removal is the correction path and
 * the owning order/batch recalculates its status afterwards.
 */
class PaymentRecordDeleteTest extends TestCase
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

    public function test_order_payment_can_be_deleted_and_order_recalculates(): void
    {
        $this->asAdmin();

        $order = Order::factory()->payLater()->create([
            'status' => 'completed',
            'total_amount' => 100000,
        ]);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 60000,
            'payment_date' => now(),
            'payment_method' => 'cash',
        ]);

        $finalPayment = OrderPayment::create([
            'order_id' => $order->id,
            'amount' => 40000,
            'payment_date' => now(),
            'payment_method' => 'cash',
        ]);

        $this->assertSame('completed', $order->fresh()->status->value);

        Livewire::test(RiwayatBayar::class, ['record' => $order->id])
            ->callAction($this->deleteAction($finalPayment));

        $this->assertDatabaseMissing('order_payments', ['id' => $finalPayment->id]);
        $this->assertSame('unpaid', $order->fresh()->status->value);
    }

    public function test_batch_payment_can_be_deleted_and_batch_recalculates(): void
    {
        $this->asAdmin();

        $ingredient = Ingredient::factory()->create(['batch_mode' => 'fefo']);

        $batch = IngredientBatch::create([
            'ingredient_id' => $ingredient->id,
            'quantity' => 10,
            'initial_quantity' => 10,
            'total_cost' => 50000,
            'payment_status' => 'unpaid',
            'received_at' => now(),
        ]);

        $payment = BatchPayment::create([
            'ingredient_batch_id' => $batch->id,
            'amount' => 50000,
            'payment_date' => now(),
            'payment_method' => 'cash',
        ]);

        $this->assertSame('paid', $batch->fresh()->payment_status->value);

        Livewire::test(RiwayatBayarBatch::class, ['record' => $batch->id])
            ->callAction($this->deleteAction($payment));

        $this->assertDatabaseMissing('batch_payments', ['id' => $payment->id]);
        $this->assertSame('unpaid', $batch->fresh()->payment_status->value);
    }
}
