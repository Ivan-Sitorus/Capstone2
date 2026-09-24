<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\ReceivableResource\Pages\RiwayatBayar;
use App\Filament\Resources\StockResource\Pages\RiwayatBayarBatch;
use App\Models\Ingredient;
use App\Models\IngredientBatch;
use App\Models\Order;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentSummaryRefreshTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin, 'admin');

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        return $admin;
    }

    public function test_receivable_summary_refreshes_after_recording_payment(): void
    {
        $this->actingAsAdmin();

        $order = Order::factory()->payLater()->create([
            'status' => 'unpaid',
            'total_amount' => 100000,
        ]);

        $component = Livewire::test(RiwayatBayar::class, ['record' => $order->id])
            ->assertOk()
            ->assertSee('Rp100.000');

        $component->mountAction('catat_pembayaran')
            ->set('mountedActions.0.data.amount', 40000)
            ->set('mountedActions.0.data.payment_method', 'cash')
            ->set('mountedActions.0.data.payment_date', now()->toDateTimeString())
            ->callMountedAction()
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('order_payments', [
            'order_id' => $order->id,
            'amount' => 40000,
        ]);

        $component
            ->assertSee('Rp40.000')
            ->assertSee('Rp60.000');
    }

    public function test_payable_summary_refreshes_after_recording_payment(): void
    {
        $this->actingAsAdmin();

        $ingredient = Ingredient::factory()->create(['batch_mode' => 'fefo']);

        $batch = IngredientBatch::factory()->create([
            'ingredient_id' => $ingredient->id,
            'total_cost' => 100000,
            'payment_status' => 'unpaid',
        ]);

        $component = Livewire::test(RiwayatBayarBatch::class, ['record' => $batch->id])
            ->assertOk()
            ->assertSee('Rp100.000');

        $component->mountAction('catat_pembayaran')
            ->set('mountedActions.0.data.amount', 30000)
            ->set('mountedActions.0.data.payment_method', 'cash')
            ->set('mountedActions.0.data.payment_date', now()->toDateTimeString())
            ->callMountedAction()
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('batch_payments', [
            'ingredient_batch_id' => $batch->id,
            'amount' => 30000,
        ]);

        $component
            ->assertSee('Rp30.000')
            ->assertSee('Rp70.000');
    }
}
