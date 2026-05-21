<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

class RingkasanClusteringBahanBaku extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Ringkasan Klasterisasi Bahan Baku';

    protected static ?string $title = 'Ringkasan Klasterisasi Bahan Baku';

    protected static ?int $navigationSort = 10;

    // ── State ──────────────────────────────────────────────────────────
    public bool    $hasResult       = false;
    public ?string $lastRunAt       = null;
    public ?string $errorMsg        = null;

    public int    $bestK             = 0;
    public float  $silhouetteScore   = 0.0;
    public int    $totalIngredients  = 0;
    public string $dateFrom          = '';
    public string $dateTo            = '';
    public array  $clusters          = [];
    public array  $tableRows         = [];

    public ?string $chartBar        = null;
    public ?string $chartElbow      = null;
    public ?string $chartSilhouette = null;

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
        $this->loadFromCache();
    }

    public function refreshData(): void
    {
        $this->errorMsg = null;
        $cached = Cache::get('klasterisasi_bahan_baku_last_result');

        if (! $cached) {
            $this->errorMsg  = 'Belum ada hasil clustering. Silakan jalankan Klasterisasi Bahan Baku terlebih dahulu.';
            $this->hasResult = false;

            Notification::make()
                ->title('Belum ada data')
                ->body('Jalankan proses di halaman Klasterisasi Bahan Baku dulu.')
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
        $cached = Cache::get('klasterisasi_bahan_baku_last_result');
        if (! $cached) return;

        $this->bestK            = $cached['best_k']              ?? 0;
        $this->silhouetteScore  = $cached['silhouette_score']    ?? 0.0;
        $this->totalIngredients = $cached['total_ingredients']   ?? 0;
        $this->dateFrom         = $cached['date_range']['from']  ?? '';
        $this->dateTo           = $cached['date_range']['to']    ?? '';
        $this->clusters         = $cached['clusters']            ?? [];
        $this->tableRows        = $cached['table_rows']          ?? [];
        $this->chartBar         = $cached['charts']['bar']        ?? null;
        $this->chartElbow       = $cached['charts']['elbow']      ?? null;
        $this->chartSilhouette  = $cached['charts']['silhouette'] ?? null;
        $this->lastRunAt        = $cached['last_run_at']           ?? null;
        $this->hasResult        = true;
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