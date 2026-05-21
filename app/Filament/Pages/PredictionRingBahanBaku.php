<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

class PredictionRingBahanBaku extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Prediction Ring Bahan Baku';

    protected static ?string $title = 'Prediction Ring Bahan Baku';

    protected static ?int $navigationSort = 10;

    // ── State ──────────────────────────────────────────────────────────
    public bool    $hasResult  = false;
    public ?string $lastRunAt  = null;
    public ?string $errorMsg   = null;

    // ── Hasil prediksi (diambil dari cache) ────────────────────────────
    public int    $totalIngredients    = 0;
    public int    $forecastDays        = 0;
    public string $dateFrom            = '';
    public string $dateTo              = '';
    public string $dateForecastFrom    = '';
    public string $dateForecastTo      = '';
    public array  $predictions         = [];
    public array  $summaryTable        = [];
    public array  $preprocessLogs      = [];

    // ── Grafik (base64 PNG) ────────────────────────────────────────────
    public ?string $chartForecastAll       = null;
    public ?string $chartFeatureImportance = null;
    public ?string $chartEvaluation        = null;
    public ?string $chartAllItems          = null;
    public array   $chartPerIngredient     = [];

    public function getView(): string
    {
        return 'filament.pages.prediction-ring-bahan-baku';
    }

    public function getTitle(): string
    {
        return 'Prediction Ring Bahan Baku';
    }

    // ── Load dari cache saat halaman dibuka ───────────────────────────
    public function mount(): void
    {
        $this->loadFromCache();
    }

    // ── Load hasil prediksi dari cache ────────────────────────────────
    public function loadFromCache(): void
    {
        $cached = Cache::get('prediksi_bahan_baku_last_result');

        if (! $cached) {
            $this->hasResult = false;
            return;
        }

        $this->totalIngredients = $cached['total_ingredients']       ?? 0;
        $this->forecastDays     = $cached['forecast_days']           ?? 0;
        $this->dateFrom         = $cached['date_range']['from']      ?? '';
        $this->dateTo           = $cached['date_range']['to']        ?? '';
        $this->dateForecastFrom = $cached['forecast_range']['from']  ?? '';
        $this->dateForecastTo   = $cached['forecast_range']['to']    ?? '';
        $this->predictions      = $cached['predictions']             ?? [];
        $this->summaryTable     = $cached['summary_table']           ?? [];
        $this->preprocessLogs   = $cached['preprocessing_logs']      ?? [];

        $charts = $cached['charts'] ?? [];
        $this->chartForecastAll       = $charts['forecast_all']       ?? null;
        $this->chartFeatureImportance = $charts['feature_importance'] ?? null;
        $this->chartEvaluation        = $charts['evaluation']         ?? null;
        $this->chartAllItems          = $charts['all_items']          ?? null;
        $this->chartPerIngredient     = $charts['per_ingredient']     ?? [];

        $this->lastRunAt = $cached['last_run_at'] ?? null;
        $this->hasResult = true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Perbarui Data Prediksi Penggunaan Bahan Baku')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $this->loadFromCache();

                    if ($this->hasResult) {
                        Notification::make()
                            ->title('Data diperbarui')
                            ->body('Menampilkan hasil prediksi bahan baku terakhir.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Belum ada data prediksi')
                            ->body('Silakan jalankan prediksi terlebih dahulu di halaman Prediksi Bahan Baku.')
                            ->warning()
                            ->send();
                    }
                }),
        ];
    }
}