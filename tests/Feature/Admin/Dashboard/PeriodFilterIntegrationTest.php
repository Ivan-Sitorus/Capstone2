<?php

namespace Tests\Feature\Admin\Dashboard;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PeriodFilterIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_today_period_shows_only_todays_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Order::factory(3)->create([
            'is_paid' => true,
            'total_amount' => 10000,
            'created_at' => now(),
        ]);

        Order::factory(2)->create([
            'is_paid' => true,
            'total_amount' => 10000,
            'created_at' => now()->subDay(),
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\SalesOverview::class, [
                'pageFilters' => ['period' => 'today'],
            ])
            ->assertSee('Total Pendapatan');
    }

    public function test_week_period_shows_current_week_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Order::factory(3)->create([
            'is_paid' => true,
            'total_amount' => 10000,
            'created_at' => now(),
        ]);

        Order::factory(2)->create([
            'is_paid' => true,
            'total_amount' => 10000,
            'created_at' => now()->subWeek(),
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\SalesOverview::class, [
                'pageFilters' => ['period' => 'this_week'],
            ])
            ->assertSee('Total Pendapatan');
    }

    public function test_month_period_shows_current_month_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Order::factory(3)->create([
            'is_paid' => true,
            'total_amount' => 10000,
            'created_at' => now(),
        ]);

        Order::factory(2)->create([
            'is_paid' => true,
            'total_amount' => 10000,
            'created_at' => now()->subMonth(),
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\SalesOverview::class, [
                'pageFilters' => ['period' => 'this_month'],
            ])
            ->assertSee('Total Pendapatan');
    }

    public function test_period_filter_affects_multiple_widgets_consistently(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Order::factory(3)->create([
            'is_paid' => true,
            'total_amount' => 15000,
            'created_at' => now(),
        ]);

        Order::factory(5)->create([
            'is_paid' => true,
            'total_amount' => 10000,
            'created_at' => now()->subDays(3),
        ]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Widgets\SalesOverview::class, [
                'pageFilters' => ['period' => 'today'],
            ])
            ->assertSee('Total Pendapatan');
    }
}
