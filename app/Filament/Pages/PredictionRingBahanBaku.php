<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\DataminingRun;

class PredictionRingBahanBaku extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Prediction Ring Bahan Baku';

    protected static ?string $title = 'Prediction Ring Bahan Baku';

    protected static ?int $navigationSort = 15;

    // ── State ──────────────────────────────────────────────────────────
    public bool  $hasResult = false;

    // Menyimpan maks. 3 hasil prediksi terakhir (rentang tanggal berbeda)
    public array $results = [];

    public function getView(): string
    {
        return 'filament.pages.prediction-ring-bahan-baku';
    }

    public function getTitle(): string
    {
        return 'Prediction Ring Bahan Baku';
    }

    // ── Load saat halaman pertama kali dibuka ──────────────────────────
    public function mount(): void
    {
        $this->loadHistory();
    }

    // ── Ambil history dari datamining_runs ─────────────────────────────
    public function loadHistory(): void
    {
        $history = DataminingRun::completedHistory('prediction-bahan-baku', 3)
            ->map(function (DataminingRun $run) {
                $payload = $run->payload ?? [];
                $params  = $run->parameters ?? [];

                return [
                    'run_at'                   => $run->created_at?->locale('id')->translatedFormat('d M Y, H:i'),
                    'input_date_from'          => $params['date_from'] ?? '',
                    'input_date_to'            => $params['date_to'] ?? '',
                    'date_from'                => $payload['date_range']['from'] ?? '',
                    'date_to'                  => $payload['date_range']['to'] ?? '',
                    'date_forecast_from'       => $payload['forecast_range']['from'] ?? '',
                    'date_forecast_to'         => $payload['forecast_range']['to'] ?? '',
                    'total_ingredients'        => $payload['total_ingredients'] ?? 0,
                    'forecast_days'            => $payload['forecast_days'] ?? 0,
                    'predictions'              => $payload['predictions'] ?? [],
                    'summary_table'            => $payload['summary_table'] ?? [],
                    'chart_feature_importance' => null,
                ];
            })
            ->values()
            ->all();

        if (empty($history)) {
            $this->hasResult = false;
            $this->results   = [];
            return;
        }

        $this->results   = $history;
        $this->hasResult = true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Perbarui Data Prediksi Penggunaan Bahan Baku')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $this->loadHistory();

                    if ($this->hasResult) {
                        $count = count($this->results);
                        Notification::make()
                            ->title('Data diperbarui')
                            ->body("Menampilkan {$count} laporan prediksi bahan baku terakhir.")
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
