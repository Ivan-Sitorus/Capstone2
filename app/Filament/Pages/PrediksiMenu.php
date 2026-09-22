<?php

namespace App\Filament\Pages;

use App\Models\DataminingRun;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class PrediksiMenu extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Prediksi Menu';

    protected static ?string $title = 'Prediksi Menu';

    protected static ?int $navigationSort = 13;

    // ── Input rentang tanggal dari admin ───────────────────────────────
    public string $inputDateFrom  = '';
    public string $inputDateTo    = '';
    public string $dateRangeError = '';

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
    public array  $predictions        = [];
    public array  $summaryTable       = [];
    public array  $preprocessLogs     = [];

    // ── Grafik ─────────────────────────────────────────────────────────
    public ?string $chartForecastAll       = null;
    public ?string $chartFeatureImportance = null;
    public ?string $chartAllItems          = null;
    public array   $chartPerMenu           = [];

    public function getView(): string
    {
        return 'filament.pages.prediksi-menu';
    }

    public function getTitle(): string
    {
        return 'Prediksi Menu';
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

    // ── Panggil FastAPI endpoint prediksi ──────────────────────────────
    public function runPrediction(): void
    {
        $this->errorMsg       = null;
        $this->dateRangeError = '';

        if (! $this->inputDateFrom || ! $this->inputDateTo) {
            $this->dateRangeError = 'Harap isi rentang tanggal data penjualan terlebih dahulu.';
            return;
        }

        $from = Carbon::parse($this->inputDateFrom);
        $to   = Carbon::parse($this->inputDateTo);

        if ($to->lessThanOrEqualTo($from)) {
            $this->dateRangeError = 'Tanggal akhir harus lebih besar dari tanggal awal.';
            return;
        }

        if ($from->diffInMonths($to) < 3) {
            $this->dateRangeError = 'Rentang tanggal data penjualan minimal 3 bulan.';
            return;
        }

        $this->hasResult = false;

        try {
            app(\App\Services\DataMiningRunner::class)->dispatch('prediction', $this->inputDateFrom, $this->inputDateTo);

            Notification::make()
                ->title('Prediksi sedang diproses')
                ->body('Hasil akan muncul otomatis setelah selesai diproses.')
                ->info()
                ->send();
        } catch (\Throwable $e) {
            report($e);
            $this->errorMsg = $e->getMessage();

            Notification::make()
                ->title('Prediksi gagal dimulai')
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
        $run = DataminingRun::latestCompleted('prediction');

        if (! $run) {
            return;
        }

        $this->hydrateResult($run->payload ?? []);
        $this->lastRunAt = $run->created_at?->locale('id')->translatedFormat('d M Y, H:i');
    }

    protected function hydrateResult(array $data): void
    {
        $this->totalMenu        = $data['total_menu']             ?? 0;
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
        $this->chartAllItems          = $charts['all_items']          ?? null;
        $this->chartPerMenu           = $charts['per_menu']           ?? [];

        $this->hasResult = true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run_prediction')
                ->label('Jalankan Prediksi')
                ->icon('heroicon-o-sparkles')
                ->color('primary')
                ->disabled(fn() => ! $this->isDateRangeValid())
                ->action($this->runPrediction(...)),
        ];
    }
}
