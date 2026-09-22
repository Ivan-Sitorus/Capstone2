<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\DataminingRun;

class PrediksiBahanBaku extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Prediksi Bahan Baku';

    protected static ?string $title = 'Prediksi Penggunaan Bahan Baku';

    protected static ?int $navigationSort = 14;

    // ── Input rentang tanggal dari admin ───────────────────────────────
    public string $inputDateFrom  = '';
    public string $inputDateTo    = '';
    public string $dateRangeError = '';

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
        return 'filament.pages.prediksi-bahan-baku';
    }

    public function getTitle(): string
    {
        return 'Prediksi Penggunaan Bahan Baku';
    }

    // ── Validasi rentang tanggal (min 3 bulan) ─────────────────────────
    public function isDateRangeValid(): bool
    {
        if (! $this->inputDateFrom || ! $this->inputDateTo) {
            return false;
        }
        try {
            $from = Carbon::parse($this->inputDateFrom);
            $to   = Carbon::parse($this->inputDateTo);
            return $to->greaterThan($from) && $from->diffInMonths($to) >= 3;
        } catch (\Throwable) {
            return false;
        }
    }

    // ── Panggil FastAPI endpoint prediksi bahan baku ───────────────────
    public function runPrediction(): void
    {
        $this->errorMsg       = null;
        $this->dateRangeError = '';

        if (! $this->inputDateFrom || ! $this->inputDateTo) {
            $this->dateRangeError = 'Harap isi rentang tanggal data penggunaan bahan baku terlebih dahulu.';
            return;
        }

        $from = Carbon::parse($this->inputDateFrom);
        $to   = Carbon::parse($this->inputDateTo);

        if ($to->lessThanOrEqualTo($from)) {
            $this->dateRangeError = 'Tanggal akhir harus lebih besar dari tanggal awal.';
            return;
        }

        if ($from->diffInMonths($to) < 3) {
            $this->dateRangeError = 'Rentang tanggal data penggunaan bahan baku minimal 3 bulan.';
            return;
        }

        $this->hasResult = false;

        try {
            app(\App\Services\DataMiningRunner::class)->dispatch('prediction-bahan-baku', $this->inputDateFrom, $this->inputDateTo);

            Notification::make()
                ->title('Prediksi Bahan Baku sedang diproses')
                ->body('Hasil akan muncul otomatis setelah selesai diproses.')
                ->info()
                ->send();
        } catch (\Throwable $e) {
            report($e);
            $this->errorMsg = $e->getMessage();

            Notification::make()
                ->title('Prediksi Bahan Baku gagal dimulai')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function mount(): void
    {
        $this->loadLatestResult();
    }

    public function loadLatestResult(): void
    {
        $run = DataminingRun::latest('prediction-bahan-baku');

        if (! $run) {
            return;
        }

        if ($run->status === 'completed') {
            $this->hydrateResult($run->payload ?? []);
            $this->lastRunAt = $run->created_at?->locale('id')->translatedFormat('d M Y, H:i');
        } elseif ($run->status === 'failed') {
            $this->errorMsg  = $run->error;
            $this->hasResult = false;
        }
    }

    protected function hydrateResult(array $data): void
    {
        $this->totalIngredients = $data['total_ingredients']      ?? 0;
        $this->forecastDays     = $data['forecast_days']          ?? 0;
        $this->dateFrom         = $data['date_range']['from']     ?? '';
        $this->dateTo           = $data['date_range']['to']       ?? '';
        $this->dateForecastFrom = $data['forecast_range']['from'] ?? '';
        $this->dateForecastTo   = $data['forecast_range']['to']   ?? '';
        $this->predictions      = $data['predictions']            ?? [];
        $this->summaryTable     = $data['summary_table']          ?? [];
        $this->preprocessLogs   = $data['preprocessing_logs']     ?? [];

        $charts = $data['charts'] ?? [];
        $this->chartForecastAll       = $charts['forecast_all']       ?? null;
        $this->chartFeatureImportance = $charts['feature_importance'] ?? null;
        $this->chartEvaluation        = $charts['evaluation']         ?? null;
        $this->chartAllItems          = $charts['all_items']          ?? null;
        $this->chartPerIngredient     = $charts['per_ingredient']     ?? [];

        $this->hasResult = true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run_prediction_bahan_baku')
                ->label('Jalankan Prediksi Bahan Baku')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->disabled(fn() => ! $this->isDateRangeValid())
                ->action($this->runPrediction(...)),
        ];
    }
}
