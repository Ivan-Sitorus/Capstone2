<?php

namespace Tests\Feature\Admin\Dashboard;

use App\Filament\Pages\Dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_page_loads_successfully(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('filament.admin.pages.dashboard'));

        $response->assertStatus(200);
    }

    public function test_dashboard_has_no_data_mining_widget_content(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $panel = \Filament\Facades\Filament::getPanel('admin');
        \Filament\Facades\Filament::setCurrentPanel($panel);

        Livewire::actingAs($admin)
            ->test(Dashboard::class)
            ->assertDontSee('Total Prediksi per Menu')
            ->assertDontSee('Total Prediksi per Bahan Baku')
            ->assertDontSee('Data Mining');
    }
}
