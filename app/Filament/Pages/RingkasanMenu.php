<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

class RingkasanMenu extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Ringkasan Menu';

    protected static ?string $title = 'Ringkasan Menu';

    protected static ?int $navigationSort = 10;

    // ── State ──────────────────────────────────────────────────────────
    public bool    $hasResult  = false;
    public ?string $lastRunAt  = null;
    public ?string $errorMsg   = null;

    public int     $bestK           = 0;
    public float   $silhouetteScore = 0.0;
    public int     $totalMenu       = 0;
    public string  $dateFrom        = '';
    public string  $dateTo          = '';
    public array   $tableRows       = [];
    public ?string $chartBar        = null;
    public ?string $chartElbow      = null;
    public ?string $chartSilhouette = null;

    public function getView(): string
    {
        return 'filament.pages.ringkasan-menu';
    }

    public function getTitle(): string
    {
        return 'Ringkasan Menu';
    }

    public function mount(): void
    {
        // Otomatis load data cache saat halaman dibuka
        $this->loadFromCache();
    }

    public function refreshClusteringData(): void
    {
        $this->errorMsg = null;
        $cached = Cache::get('klasterisasi_menu_last_result');

        if (! $cached) {
            $this->errorMsg  = 'Belum ada hasil clustering. Silakan jalankan Klasterisasi Menu Penjualan terlebih dahulu.';
            $this->hasResult = false;

            Notification::make()
                ->title('Belum ada data')
                ->body('Jalankan proses di halaman Klasterisasi Menu Penjualan dulu.')
                ->warning()
                ->send();
            return;
        }

        $this->loadFromCache();

        Notification::make()
            ->title('Data diperbarui')
            ->body('Menampilkan hasil klasterisasi terakhir: ' . ($this->lastRunAt ?? '-'))
            ->success()
            ->send();
    }

    private function loadFromCache(): void
    {
        $cached = Cache::get('klasterisasi_menu_last_result');
        if (! $cached) return;

        $this->bestK           = $cached['best_k']           ?? 0;
        $this->silhouetteScore = $cached['silhouette_score'] ?? 0.0;
        $this->totalMenu       = $cached['total_menu']       ?? 0;
        $this->dateFrom        = $cached['date_range']['from'] ?? '';
        $this->dateTo          = $cached['date_range']['to']   ?? '';
        $this->tableRows       = $cached['table_rows']          ?? [];
        $this->chartBar        = $cached['charts']['bar']        ?? null;
        $this->chartElbow      = $cached['charts']['elbow']      ?? null;
        $this->chartSilhouette = $cached['charts']['silhouette']  ?? null;
        $this->lastRunAt       = $cached['last_run_at']           ?? null;
        $this->hasResult       = true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh_clustering')
                ->label('Perbarui Data Klasterisasi Menu')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(fn() => $this->refreshClusteringData()),
        ];
    }
}