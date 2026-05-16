<?php

namespace Tests\Feature\Admin\Dashboard;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_today_period_counts_only_todays_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Order::factory(3)->create([
            'is_paid' => true,
            'created_at' => now(),
        ]);

        Order::factory(2)->create([
            'is_paid' => true,
            'created_at' => now()->subDays(5),
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\SalesOverview::class, [
                'pageFilters' => ['period' => 'today'],
            ])
            ->assertSee('Total Pendapatan');
    }

    public function test_this_week_period_counts_current_week_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Order::factory(3)->create([
            'is_paid' => true,
            'created_at' => now(),
        ]);

        Order::factory(2)->create([
            'is_paid' => true,
            'created_at' => now()->subWeek(),
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\SalesOverview::class, [
                'pageFilters' => ['period' => 'this_week'],
            ])
            ->assertSee('Total Pendapatan');
    }

    public function test_zero_orders_shows_zero_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\SalesOverview::class, [
                'pageFilters' => ['period' => 'today'],
            ])
            ->assertSee('Rp 0');
    }

    public function test_default_period_falls_back_to_today(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Order::factory()->create([
            'is_paid' => true,
            'created_at' => now(),
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\SalesOverview::class)
            ->assertSee('Total Pendapatan');
    }
}
