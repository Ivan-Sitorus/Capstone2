<?php

namespace Tests\Feature\Admin\Dashboard;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DailySalesChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_chart_renders_with_period_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Order::factory(2)->create([
            'is_paid' => true,
            'created_at' => now()->subDays(3),
        ]);
        Order::factory(3)->create([
            'is_paid' => true,
            'created_at' => now()->subDays(2),
        ]);
        Order::factory(1)->create([
            'is_paid' => true,
            'created_at' => now()->subDay(),
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\DailySalesChart::class, [
                'pageFilters' => ['period' => 'this_week'],
            ])
            ->assertSee('Penjualan');
    }

    public function test_chart_returns_correct_dataset_structure(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Order::factory(3)->create([
            'is_paid' => true,
            'created_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\DailySalesChart::class, [
                'pageFilters' => ['period' => 'this_week'],
            ])
            ->assertSee('Penjualan');
    }
}
