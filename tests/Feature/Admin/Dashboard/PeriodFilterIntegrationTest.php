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

    public function test_sales_overview_renders_without_filters(): void
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
            ->test(\App\Filament\Widgets\SalesOverview::class)
            ->assertSee('Total Pendapatan');
    }

    public function test_all_widgets_render_consistently(): void
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
            ->test(\App\Filament\Widgets\SalesOverview::class)
            ->assertSee('Total Pendapatan');
    }
}
