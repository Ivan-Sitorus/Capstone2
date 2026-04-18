<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class AsosiatifMenu extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Asosiatif Menu';

    protected static ?string $title = 'Asosiatif Menu';

    protected static ?int $navigationSort = 12;

    // ── State ──────────────────────────────────────────────────────────
    public bool    $hasResult = false;
    public ?string $lastRunAt = null;
    public ?string $errorMsg  = null;

    // ── Hasil association rule ─────────────────────────────────────────
    public int    $totalRules        = 0;
    public int    $totalTransactions = 0;
    public float  $minSupport        = 0.0;
    public float  $minConfidence     = 0.0;
    public string $dateFrom          = '';
    public string $dateTo            = '';
    public array  $rules             = [];       // TOP 8 rules (menu_pertama, menu_kedua, ...)
    public array  $freq1Itemsets     = [];       // frequent 1-itemsets
    public array  $freq2Itemsets     = [];       // frequent 2-itemsets
    public array  $freq3Itemsets     = [];       // frequent 3-itemsets
    public array  $preprocessLogs    = [];

    // ── Grafik ─────────────────────────────────────────────────────────
    public ?string $chartSupConf  = null;   // scatter support vs confidence
    public ?string $chartTopRules = null;   // bar top-N rules by lift
    public ?string $chartFreqItem = null;   // bar frequent 1-itemsets

    public function getView(): string
    {
        return 'filament.pages.asosiatif-menu';
    }

    public function getTitle(): string
    {
        return 'Asosiatif Menu';
    }

    // ── Panggil FastAPI endpoint association rule ──────────────────────
    public function runAssociation(): void
    {
        $this->errorMsg = null;

        try {
            // TODO: Ganti URL jika endpoint berbeda
            $response = Http::timeout(120)->post('http://127.0.0.1:8001/association');

            if (! $response->successful()) {
                throw new \Exception('FastAPI merespons dengan status ' . $response->status());
            }

            $data = $response->json();

            if (($data['status'] ?? '') === 'error') {
                throw new \Exception($data['message'] ?? 'Unknown error dari FastAPI');
            }

            $this->totalRules        = $data['total_rules']        ?? 0;
            $this->totalTransactions = $data['total_transactions'] ?? 0;
            $this->minSupport        = $data['min_support']        ?? 0.0;
            $this->minConfidence     = $data['min_confidence']     ?? 0.0;
            $this->dateFrom          = $data['date_range']['from'] ?? '';
            $this->dateTo            = $data['date_range']['to']   ?? '';
            $this->rules             = $data['rules']              ?? [];
            $this->freq1Itemsets     = $data['freq_1_itemsets']    ?? [];
            $this->freq2Itemsets     = $data['freq_2_itemsets']    ?? [];
            $this->freq3Itemsets     = $data['freq_3_itemsets']    ?? [];
            $this->preprocessLogs    = $data['preprocessing_logs'] ?? [];
            $this->chartSupConf      = $data['charts']['sup_conf']  ?? null;
            $this->chartTopRules     = $data['charts']['top_rules'] ?? null;
            $this->chartFreqItem     = $data['charts']['freq_item'] ?? null;

            $this->hasResult = true;
            $this->lastRunAt = now()->locale('id')->translatedFormat('d M Y, H:i');

            Notification::make()
                ->title('Association Rule selesai!')
                ->body("Ditemukan {$this->totalRules} rules dari {$this->totalTransactions} transaksi.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            $this->errorMsg  = $e->getMessage();
            $this->hasResult = false;

            Notification::make()
                ->title('Gagal menjalankan Association Rule')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run_association')
                ->label('Jalankan Association Rule')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Jalankan Association Rule Mining')
                ->modalDescription('Proses ini akan membaca data Riwayat Pesanan Kasir dan menemukan kombinasi menu yang sering dipesan secara bersamaan menggunakan algoritma Apriori / FP-Growth. Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Jalankan')
                ->action(fn() => $this->runAssociation()),
        ];
    }
}
