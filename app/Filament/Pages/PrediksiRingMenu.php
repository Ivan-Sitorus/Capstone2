<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

class PrediksiRingMenu extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Prediksi Ring Menu';

    protected static ?string $title = 'Prediksi Ring Menu';

    protected static ?int $navigationSort = 10;

    // ── State ──────────────────────────────────────────────────────────
    public bool    $hasResult  = false;
    public ?string $lastRunAt  = null;
    public ?string $errorMsg   = null;

    // ── Hasil prediksi (diambil dari cache) ────────────────────────────
    public int    $totalMenu          = 0;
    public int    $forecastDays       = 0;
    public string $dateFrom           = '';
    public string $dateTo             = '';
    public string $dateForecastFrom   = '';
    public string $dateForecastTo     = '';
    public array  $predictions        = [];
    public array  $summaryTable       = [];
    public array  $preprocessLogs     = [];

    // ── Grafik ─────────────────────────────────────────────────────────
    public ?string $chartForecastAll       = null;
    public ?string $chartFeatureImportance = null;
    public ?string $chartEvaluation        = null;
    public ?string $chartAllItems          = null;
    public array   $chartPerMenu           = [];

    public function getView(): string
    {
        return 'filament.pages.prediksi-ring-menu';
    }

    public function getTitle(): string
    {
        return 'Prediksi Ring Menu';
    }

    // ── Load dari cache saat halaman dibuka ───────────────────────────
    public function mount(): void
    {
        $this->loadFromCache();
    }

    // ── Load hasil prediksi dari cache ────────────────────────────────
    public function loadFromCache(): void
    {
        $cached = Cache::get('prediksi_menu_last_result');

        if (! $cached) {
            $this->hasResult = false;
            return;
        }

        $this->totalMenu          = $cached['total_menu']              ?? 0;
        $this->forecastDays       = $cached['forecast_days']           ?? 0;
        $this->dateFrom           = $cached['date_range']['from']      ?? '';
        $this->dateTo             = $cached['date_range']['to']        ?? '';
        $this->dateForecastFrom   = $cached['forecast_range']['from']  ?? '';
        $this->dateForecastTo     = $cached['forecast_range']['to']    ?? '';
        $this->predictions        = $cached['predictions']             ?? [];
        $this->summaryTable       = $cached['summary_table']           ?? [];
        $this->preprocessLogs     = $cached['preprocessing_logs']      ?? [];

        $charts = $cached['charts'] ?? [];
        $this->chartForecastAll       = $charts['forecast_all']       ?? null;
        $this->chartFeatureImportance = $charts['feature_importance'] ?? null;
        $this->chartEvaluation        = $charts['evaluation']         ?? null;
        $this->chartAllItems          = $charts['all_items']          ?? null;
        $this->chartPerMenu           = $charts['per_menu']           ?? [];

        $this->lastRunAt = $cached['last_run_at'] ?? null;
        $this->hasResult = true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Perbarui Data Prediksi Penjualan Menu')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $this->loadFromCache();

                    if ($this->hasResult) {
                        Notification::make()
                            ->title('Data diperbarui')
                            ->body('Menampilkan hasil prediksi menu terakhir.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Belum ada data prediksi')
                            ->body('Silakan jalankan prediksi terlebih dahulu di halaman Prediksi Menu.')
                            ->warning()
                            ->send();
                    }
                }),
        ];
    }
}