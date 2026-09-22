<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\DataminingRun;

class AsosiatifMenu extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Asosiatif Menu';

    protected static ?string $title = 'Asosiatif Menu';

    protected static ?int $navigationSort = 12;

    // ── Input pengguna ─────────────────────────────────────────────────────
    public string $inputDateFrom = '';
    public string $inputDateTo   = '';

    // ── State ──────────────────────────────────────────────────────────────
    public bool    $hasResult = false;
    public ?string $lastRunAt = null;
    public ?string $errorMsg  = null;

    // ── Tanggal yang digunakan saat run (berbeda dari input aktif) ──────────
    public string $usedDateFrom = '';
    public string $usedDateTo   = '';

    // ── Hasil association rule ─────────────────────────────────────────────
    public int    $totalRules        = 0;
    public int    $totalTransactions = 0;
    public float  $minSupport        = 0.0;
    public float  $minConfidence     = 0.0;
    public string $dateFrom          = '';
    public string $dateTo            = '';
    public array  $rules             = [];
    public array  $freq1Itemsets     = [];
    public array  $freq2Itemsets     = [];
    public array  $preprocessLogs    = [];

    // ── Grafik ─────────────────────────────────────────────────────────────
    public ?string $chartSupConf  = null;
    public ?string $chartTopRules = null;
    public ?string $chartFreqItem = null;

    public function getView(): string
    {
        return 'filament.pages.asosiatif-menu';
    }

    public function getTitle(): string
    {
        return 'Asosiatif Menu';
    }

    // ── Validasi rentang tanggal (minimal 3 bulan) ─────────────────────────
    public function isDatesValid(): bool
    {
        if (empty($this->inputDateFrom) || empty($this->inputDateTo)) {
            return false;
        }
        try {
            $from = Carbon::parse($this->inputDateFrom);
            $to   = Carbon::parse($this->inputDateTo);
            return $to->gt($from) && $from->copy()->addMonths(3)->lte($to);
        } catch (\Throwable) {
            return false;
        }
    }

    // ── Panggil FastAPI endpoint association rule ──────────────────────────
    public function runAssociation(): void
    {
        if (! $this->isDatesValid()) {
            Notification::make()
                ->title('Rentang tanggal belum valid')
                ->body('Isi "Dari Tanggal" dan "Sampai Tanggal" dengan rentang minimal 3 bulan.')
                ->warning()
                ->send();
            return;
        }

        $this->errorMsg  = null;
        $this->hasResult = false;

        try {
            app(\App\Services\DataMiningRunner::class)->dispatch('association', $this->inputDateFrom, $this->inputDateTo);

            Notification::make()
                ->title('Association Rule sedang diproses')
                ->body('Hasil akan muncul otomatis setelah selesai diproses.')
                ->info()
                ->send();
        } catch (\Throwable $e) {
            report($e);
            $this->errorMsg = $e->getMessage();

            Notification::make()
                ->title('Gagal memulai Association Rule')
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
        $run = DataminingRun::latest('association');

        if (! $run) {
            return;
        }

        if ($run->status === 'completed') {
            $this->hydrateResult($run->payload ?? []);
            $this->lastRunAt    = $run->created_at?->locale('id')->translatedFormat('d M Y, H:i');
            $this->usedDateFrom = $run->parameters['date_from'] ?? '';
            $this->usedDateTo   = $run->parameters['date_to'] ?? '';
        } elseif ($run->status === 'failed') {
            $this->errorMsg  = $run->error;
            $this->hasResult = false;
        }
    }

    protected function hydrateResult(array $data): void
    {
        $this->totalRules        = $data['total_rules']        ?? 0;
        $this->totalTransactions = $data['total_transactions'] ?? 0;
        $this->minSupport        = $data['min_support']        ?? 0.0;
        $this->minConfidence     = $data['min_confidence']     ?? 0.0;
        $this->dateFrom          = $data['date_range']['from'] ?? '';
        $this->dateTo            = $data['date_range']['to']   ?? '';
        $this->rules             = $data['rules']              ?? [];
        $this->freq1Itemsets     = $data['freq_1_itemsets']    ?? [];
        $this->freq2Itemsets     = $data['freq_2_itemsets']    ?? [];
        $this->preprocessLogs    = $data['preprocessing_logs'] ?? [];

        $charts = $data['charts'] ?? [];
        $this->chartSupConf  = $charts['sup_conf']  ?? null;
        $this->chartTopRules = $charts['top_rules'] ?? null;
        $this->chartFreqItem = $charts['freq_item'] ?? null;

        $this->hasResult = true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run_association')
                ->label('Jalankan Association Rule')
                ->icon('heroicon-o-play')
                ->color('primary')
                ->action(fn () => $this->runAssociation()),
        ];
    }
}
