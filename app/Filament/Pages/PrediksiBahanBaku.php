<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class PrediksiBahanBaku extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Prediksi Bahan Baku';

    protected static ?string $title = 'Prediksi Penggunaan Bahan Baku';

    protected static ?int $navigationSort = 14;

    // ── State ──────────────────────────────────────────────────────────
    public bool    $hasResult = false;
    public ?string $lastRunAt = null;
    public ?string $errorMsg  = null;

    // ── Hasil prediksi ─────────────────────────────────────────────────
    public int    $totalIngredients    = 0;
    public int    $forecastDays        = 0;
    public string $dateFrom            = '';
    public string $dateTo              = '';
    public string $dateForecastFrom    = '';
    public string $dateForecastTo      = '';
    public array  $predictions         = [];   // per-bahan: nama, satuan, forecast, mae, rmse, mape, smape
    public array  $summaryTable        = [];   // ringkasan semua bahan baku
    public array  $preprocessLogs      = [];

    // ── Grafik (base64 PNG) ────────────────────────────────────────────
    public ?string $chartForecastAll       = null;
    public ?string $chartFeatureImportance = null;
    public ?string $chartEvaluation        = null;
    public ?string $chartAllItems          = null;
    public array   $chartPerIngredient     = [];

    public function getView(): string
    {
        return 'filament.pages.prediksi-bahan-baku';
    }

    public function getTitle(): string
    {
        return 'Prediksi Penggunaan Bahan Baku';
    }

    // ── Panggil FastAPI endpoint prediksi bahan baku ───────────────────
    public function runPrediction(): void
    {
        $this->errorMsg = null;

        try {
            $response = Http::timeout(600)->post('http://127.0.0.1:8001/prediction-bahan-baku');

            if (! $response->successful()) {
                throw new \Exception('FastAPI merespons dengan status ' . $response->status());
            }

            $data = $response->json();

            if (($data['status'] ?? '') === 'error') {
                throw new \Exception($data['message'] ?? 'Unknown error dari FastAPI');
            }

            $this->totalIngredients = $data['total_ingredients']       ?? 0;
            $this->forecastDays     = $data['forecast_days']           ?? 0;
            $this->dateFrom         = $data['date_range']['from']      ?? '';
            $this->dateTo           = $data['date_range']['to']        ?? '';
            $this->dateForecastFrom = $data['forecast_range']['from']  ?? '';
            $this->dateForecastTo   = $data['forecast_range']['to']    ?? '';
            $this->predictions      = $data['predictions']             ?? [];
            $this->summaryTable     = $data['summary_table']           ?? [];
            $this->preprocessLogs   = $data['preprocessing_logs']      ?? [];

            $charts = $data['charts'] ?? [];
            $this->chartForecastAll       = $charts['forecast_all']       ?? null;
            $this->chartFeatureImportance = $charts['feature_importance'] ?? null;
            $this->chartEvaluation        = $charts['evaluation']         ?? null;
            $this->chartAllItems          = $charts['all_items']          ?? null;
            $this->chartPerIngredient     = $charts['per_ingredient']     ?? [];

            $this->hasResult = true;
            $this->lastRunAt = now()->locale('id')->translatedFormat('d M Y, H:i');

            Notification::make()
                ->title('Prediksi Bahan Baku selesai!')
                ->body("Berhasil memprediksi {$this->totalIngredients} bahan baku untuk {$this->forecastDays} hari ke depan.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            $this->errorMsg  = $e->getMessage();
            $this->hasResult = false;

            Notification::make()
                ->title('Prediksi Bahan Baku gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run_prediction_bahan_baku')
                ->label('Jalankan Prediksi Bahan Baku')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Jalankan Prediksi Penggunaan Bahan Baku')
                ->modalDescription('Proses ini akan membaca data pemakaian bahan baku harian dan memprediksi kebutuhan tiap bahan baku untuk beberapa hari ke depan menggunakan model Time Series Prophet. Pastikan FastAPI sudah berjalan. Proses mungkin memakan waktu beberapa menit. Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Jalankan')
                ->action(fn () => $this->runPrediction()),
        ];
    }
}
