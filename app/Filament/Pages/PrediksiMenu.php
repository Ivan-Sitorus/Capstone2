<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class PrediksiMenu extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Prediksi Menu';

    protected static ?string $title = 'Prediksi Menu';

    protected static ?int $navigationSort = 13;

    // ── State ──────────────────────────────────────────────────────────
    public bool    $hasResult = false;
    public ?string $lastRunAt = null;
    public ?string $errorMsg  = null;

    // ── Hasil prediksi ─────────────────────────────────────────────────
    public int    $totalMenu          = 0;
    public int    $forecastDays       = 0;
    public string $dateFrom           = '';
    public string $dateTo             = '';
    public string $dateForecastFrom   = '';
    public string $dateForecastTo     = '';
    public array  $predictions        = [];   // per-menu: nama, forecast, mae, rmse, mape, smape
    public array  $summaryTable       = [];   // ringkasan semua menu
    public array  $preprocessLogs     = [];

    // ── Grafik ─────────────────────────────────────────────────────────
    public ?string $chartForecastAll       = null;  // bar chart total forecast
    public ?string $chartFeatureImportance = null;  // weekday vs weekend
    public ?string $chartEvaluation        = null;  // 2×2 bar chart evaluasi
    public ?string $chartAllItems          = null;  // semua item dalam satu figure
    public array   $chartPerMenu           = [];    // per-menu: ['nama' => ..., 'chart' => base64]

    public function getView(): string
    {
        return 'filament.pages.prediksi-menu';
    }

    public function getTitle(): string
    {
        return 'Prediksi Menu';
    }

    // ── Panggil FastAPI endpoint prediksi ──────────────────────────────
    public function runPrediction(): void
    {
        $this->errorMsg = null;

        try {
            $response = Http::timeout(600)->post('http://127.0.0.1:8001/prediction');

            if (! $response->successful()) {
                throw new \Exception('FastAPI merespons dengan status ' . $response->status());
            }

            $data = $response->json();

            if (($data['status'] ?? '') === 'error') {
                throw new \Exception($data['message'] ?? 'Unknown error dari FastAPI');
            }

            $this->totalMenu        = $data['total_menu']              ?? 0;
            $this->forecastDays     = $data['forecast_days']           ?? 0;
            $this->dateFrom         = $data['date_range']['from']      ?? '';
            $this->dateTo           = $data['date_range']['to']        ?? '';
            $this->dateForecastFrom = $data['forecast_range']['from']  ?? '';
            $this->dateForecastTo   = $data['forecast_range']['to']    ?? '';
            $this->predictions      = $data['predictions']             ?? [];
            $this->summaryTable     = $data['summary_table']           ?? [];
            $this->preprocessLogs  = $data['preprocessing_logs']      ?? [];

            // Charts
            $charts = $data['charts'] ?? [];
            $this->chartForecastAll       = $charts['forecast_all']       ?? null;
            $this->chartFeatureImportance = $charts['feature_importance'] ?? null;
            $this->chartEvaluation        = $charts['evaluation']         ?? null;
            $this->chartAllItems          = $charts['all_items']          ?? null;
            $this->chartPerMenu           = $charts['per_menu']           ?? [];

            $this->hasResult = true;
            $this->lastRunAt = now()->locale('id')->translatedFormat('d M Y, H:i');

            Notification::make()
                ->title('Prediksi selesai!')
                ->body("Berhasil memprediksi {$this->totalMenu} menu untuk {$this->forecastDays} hari ke depan.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            $this->errorMsg  = $e->getMessage();
            $this->hasResult = false;

            Notification::make()
                ->title('Prediksi gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run_prediction')
                ->label('Jalankan Prediksi')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Jalankan Prediksi Penjualan Menu')
                ->modalDescription('Proses ini akan membaca data Riwayat Pesanan Kasir dan memprediksi penjualan tiap menu untuk beberapa hari ke depan menggunakan model Time Series Prophet. Proses mungkin memakan waktu beberapa menit. Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Jalankan')
                ->action($this->runPrediction(...)),
        ];
    }
}
