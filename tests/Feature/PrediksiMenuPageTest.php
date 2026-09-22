<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\PrediksiMenu;
use App\Models\DataminingRun;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PrediksiMenuPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_renders_native_filament(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        DataminingRun::create([
            'type'       => 'prediction',
            'status'     => 'completed',
            'parameters' => ['date_from' => '2025-01-01', 'date_to' => '2025-06-30'],
            'payload'    => [
                'total_menu'      => 2,
                'forecast_days'   => 2,
                'date_range'      => ['from' => '2025-01-01', 'to' => '2025-06-30'],
                'forecast_range'  => ['from' => '2025-07-01', 'to' => '2025-07-02'],
                'predictions'     => [],
                'summary_table'   => [
                    ['nama_menu' => 'Kopi Robusta', 'total_forecast' => 120.0, 'avg_per_day' => 60.0, 'mae' => 5, 'rmse' => 7, 'mape' => 10, 'smape' => 9, 'model' => 'Prophet'],
                    ['nama_menu' => 'Kopi Latte', 'total_forecast' => 90.0, 'avg_per_day' => 45.0, 'mae' => 4, 'rmse' => 6, 'mape' => 8, 'smape' => 7, 'model' => 'Prophet'],
                ],
                'preprocessing_logs' => [],
            ],
        ]);

        $this->actingAs($admin, 'admin');

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(PrediksiMenu::class)
            ->assertOk()
            ->assertSeeHtml('PredictionChartWidget')
            ->assertSeeHtml('PredictionSummaryWidget');
    }
}
