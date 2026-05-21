<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;

class RingkasanAsosiatif extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Ringkasan Asosiatif';

    protected static ?string $title = 'Ringkasan Asosiatif';

    protected static ?int $navigationSort = 10;

    // ── State ──────────────────────────────────────────────────────────
    public bool    $hasResult = false;
    public ?string $lastRunAt = null;
    public ?string $errorMsg  = null;

    public int    $totalRules        = 0;
    public int    $totalTransactions = 0;
    public float  $minSupport        = 0.0;
    public float  $minConfidence     = 0.0;
    public string $dateFrom          = '';
    public string $dateTo            = '';
    public array  $rules             = [];
    public array  $freq1Itemsets     = [];
    public array  $freq2Itemsets     = [];
    public array  $freq3Itemsets     = [];

    public ?string $chartTopRules = null;
    public ?string $chartSupConf  = null;
    public ?string $chartFreqItem = null;

    public function getView(): string
    {
        return 'filament.pages.ringkasan-asosiatif';
    }

    public function getTitle(): string
    {
        return 'Ringkasan Asosiatif';
    }

    public function mount(): void
    {
        $this->loadFromCache();
    }

    public function refreshAsosiatifData(): void
    {
        $this->errorMsg = null;
        $cached = Cache::get('asosiatif_menu_last_result');

        if (! $cached) {
            $this->errorMsg  = 'Belum ada hasil association rule. Silakan jalankan Asosiatif Menu terlebih dahulu.';
            $this->hasResult = false;

            Notification::make()
                ->title('Belum ada data')
                ->body('Jalankan proses di halaman Asosiatif Menu dulu.')
                ->warning()
                ->send();
            return;
        }

        $this->loadFromCache();

        Notification::make()
            ->title('Data diperbarui')
            ->body('Menampilkan hasil association rule terakhir: ' . ($this->lastRunAt ?? '-'))
            ->success()
            ->send();
    }

    private function loadFromCache(): void
    {
        $cached = Cache::get('asosiatif_menu_last_result');
        if (! $cached) return;

        $this->totalRules        = $cached['total_rules']        ?? 0;
        $this->totalTransactions = $cached['total_transactions'] ?? 0;
        $this->minSupport        = $cached['min_support']        ?? 0.0;
        $this->minConfidence     = $cached['min_confidence']     ?? 0.0;
        $this->dateFrom          = $cached['date_range']['from'] ?? '';
        $this->dateTo            = $cached['date_range']['to']   ?? '';
        $this->rules             = $cached['rules']              ?? [];
        $this->freq1Itemsets     = $cached['freq_1_itemsets']    ?? [];
        $this->freq2Itemsets     = $cached['freq_2_itemsets']    ?? [];
        $this->freq3Itemsets     = $cached['freq_3_itemsets']    ?? [];
        $this->chartTopRules     = $cached['charts']['top_rules'] ?? null;
        $this->chartSupConf      = $cached['charts']['sup_conf']  ?? null;
        $this->chartFreqItem     = $cached['charts']['freq_item'] ?? null;
        $this->lastRunAt         = $cached['last_run_at']          ?? null;
        $this->hasResult         = true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh_asosiatif')
                ->label('Perbarui Data Asosiatif Menu')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(fn() => $this->refreshAsosiatifData()),
        ];
    }
}