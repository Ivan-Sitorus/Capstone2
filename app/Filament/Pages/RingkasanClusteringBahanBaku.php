<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\DataminingRun;

class RingkasanClusteringBahanBaku extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Ringkasan Klasterisasi Bahan Baku';

    protected static ?string $title = 'Ringkasan Klasterisasi Bahan Baku';

    protected static ?int $navigationSort = 10;

    // ── State: array maks 3 hasil terakhir ────────────────────────────
    public array   $results   = [];
    public bool    $hasResult = false;
    public ?string $errorMsg  = null;

    public function getView(): string
    {
        return 'filament.pages.ringkasan-clustering-bahan-baku';
    }

    public function getTitle(): string
    {
        return 'Ringkasan Klasterisasi Bahan Baku';
    }

    public function mount(): void
    {
        $this->loadHistory();
    }

    public function loadHistory(): void
    {
        $history = DataminingRun::completedHistory('clustering-bahan-baku', 3)
            ->map(function (DataminingRun $run) {
                $payload = $run->payload ?? [];
                $params  = $run->parameters ?? [];

                return [
                    'run_at'             => $run->created_at?->locale('id')->translatedFormat('d M Y, H:i'),
                    'input_date_from'    => $params['date_from'] ?? '',
                    'input_date_to'      => $params['date_to'] ?? '',
                    'best_k'             => $payload['best_k']            ?? 0,
                    'silhouette_score'   => $payload['silhouette_score']  ?? 0.0,
                    'total_ingredients'  => $payload['total_ingredients'] ?? 0,
                    'date_from'          => $payload['date_range']['from'] ?? '',
                    'date_to'            => $payload['date_range']['to']   ?? '',
                    'clusters'           => $payload['clusters']          ?? [],
                    'table_rows'         => $payload['table_rows']        ?? [],
                    'rata_rata_table'    => $payload['rata_rata_table']   ?? [],
                    'preprocessing_logs' => $payload['preprocessing_logs'] ?? [],
                    'charts'             => [
                        'rata_klaster' => null,
                        'bar'          => null,
                        'elbow'        => null,
                        'silhouette'   => null,
                    ],
                ];
            })
            ->values()
            ->all();

        if (empty($history)) {
            $this->results   = [];
            $this->hasResult = false;
            return;
        }

        $this->results   = $history;
        $this->hasResult = true;
    }

    public function refreshData(): void
    {
        $this->errorMsg = null;
        $count = DataminingRun::query()
            ->where('type', 'clustering-bahan-baku')
            ->where('status', 'completed')
            ->count();

        if ($count === 0) {
            $this->errorMsg  = 'Belum ada hasil clustering. Silakan jalankan Klasterisasi Bahan Baku terlebih dahulu.';
            $this->hasResult = false;

            Notification::make()
                ->title('Belum ada data')
                ->body('Jalankan proses di halaman Klasterisasi Bahan Baku dulu.')
                ->warning()
                ->send();
            return;
        }

        $this->loadHistory();

        $latestRunAt = $this->results[0]['run_at'] ?? '-';
        Notification::make()
            ->title('Data diperbarui')
            ->body('Menampilkan ' . count($this->results) . ' hasil klasterisasi terakhir. Terbaru: ' . $latestRunAt)
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh_data')
                ->label('Perbarui Data Klasterisasi Bahan Baku')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(fn () => $this->refreshData()),
        ];
    }
}
