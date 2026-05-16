<?php

namespace Tests\Feature\Admin\Dashboard;

use App\Filament\Pages\Dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_loads(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('filament.admin.pages.dashboard'));

        $response->assertStatus(200);
    }

    public function test_dashboard_guest_redirect(): void
    {
        $response = $this->get(route('filament.admin.pages.dashboard'));

        $response->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_period_toggle_buttons_exist(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $panel = \Filament\Facades\Filament::getPanel('admin');
        \Filament\Facades\Filament::setCurrentPanel($panel);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertSee('Hari Ini')
            ->assertSee('Minggu Ini')
            ->assertSee('Bulan Ini');
    }
}
