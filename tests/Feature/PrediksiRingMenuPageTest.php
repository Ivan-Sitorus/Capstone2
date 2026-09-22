<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\PrediksiRingMenu;
use App\Models\DataminingRun;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PrediksiRingMenuPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_page_renders_native(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        DataminingRun::create([
            'type'       => 'prediction',
            'status'     => 'completed',
            'parameters' => ['date_from' => '2025-01-01', 'date_to' => '2025-06-30'],
            'payload'    => ['total_menu' => 5, 'forecast_days' => 2],
        ]);

        $this->actingAs($admin, 'admin');
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(PrediksiRingMenu::class)
            ->assertOk()
            ->assertSeeHtml('PredictionHistoryWidget');
    }
}
