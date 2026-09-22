<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Widgets\PredictionSummaryWidget;
use App\Models\DataminingRun;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PredictionSummaryWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_widget_renders_summary_table_from_datamining_run(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        DataminingRun::create([
            'type'       => 'prediction',
            'status'     => 'completed',
            'parameters' => ['date_from' => '2025-01-01', 'date_to' => '2025-06-30'],
            'payload'    => [
                'summary_table' => [
                    ['nama_menu' => 'Kopi Robusta', 'total_forecast' => 120.0, 'avg_per_day' => 60.0, 'mae' => 5, 'rmse' => 7, 'mape' => 10, 'smape' => 9, 'model' => 'Prophet'],
                ],
            ],
        ]);

        $this->actingAs($admin, 'admin');
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        Livewire::test(PredictionSummaryWidget::class)
            ->assertOk()
            ->assertSee('Kopi Robusta');
    }
}
