<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\PrediksiMenuResource\Pages\ListPrediksiMenu;
use App\Filament\Resources\PrediksiMenuResource\Pages\ViewPrediksiMenu;
use App\Models\DataminingRun;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DataminingResourceTest extends TestCase
{
    use RefreshDatabase;

    private function makeRun(): DataminingRun
    {
        return DataminingRun::create([
            'type'       => 'prediction',
            'status'     => 'completed',
            'parameters' => ['date_from' => '2025-01-01', 'date_to' => '2025-06-30'],
            'payload'    => [
                'summary_table' => [
                    ['nama_menu' => 'Kopi Robusta', 'total_forecast' => 120.0, 'avg_per_day' => 60.0, 'mae' => 5, 'rmse' => 7, 'mape' => 10, 'smape' => 9, 'model' => 'Prophet'],
                ],
            ],
        ]);
    }

    private function actingAsAdmin(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin, 'admin');

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_list_page_renders_run_history(): void
    {
        $this->actingAsAdmin();
        $this->makeRun();

        Livewire::test(ListPrediksiMenu::class)
            ->assertOk()
            ->assertSee('Jumlah Menu');
    }

    public function test_view_page_renders_result_widgets(): void
    {
        $this->actingAsAdmin();
        $run = $this->makeRun();

        Livewire::test(ViewPrediksiMenu::class, ['record' => $run->id])
            ->assertOk()
            ->assertSeeHtml('PredictionChartWidget')
            ->assertSeeHtml('PredictionSummaryWidget');
    }
}
