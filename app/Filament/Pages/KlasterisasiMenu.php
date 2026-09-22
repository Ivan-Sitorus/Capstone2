<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\DataminingRun;

class KlasterisasiMenu extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Klasterisasi Menu Penjualan';

    protected static ?string $title = 'Klasterisasi Menu Penjualan';

    protected static ?int $navigationSort = 11;

    // ── Input pengguna ─────────────────────────────────────────────────────
    public string $inputDateFrom = '';
    public string $inputDateTo   = '';

    // ── Batas kategorisasi penjualan (user-defined) ────────────────────────
    // Sangat Laris : total > sangat_laris_batas
    // Laris        : laris_batas_bawah ≤ total ≤ sangat_laris_batas
    // Cukup        : cukup_batas_bawah ≤ total ≤ (laris_batas_bawah - 1)
    // Kurang Laris : total < cukup_batas_bawah
    public int $sangat_laris_batas = 399;
    public int $laris_batas_bawah  = 350;
    public int $cukup_batas_bawah  = 200;

    // ── State ──────────────────────────────────────────────────────────────
    public bool    $isRunning = false;
    public bool    $hasResult = false;
    public ?string $lastRunAt = null;
    public ?string $errorMsg  = null;

    // ── Tanggal input yang digunakan saat run terakhir ─────────────────────
    public string $usedDateFrom = '';
    public string $usedDateTo   = '';

    // ── Hasil clustering ───────────────────────────────────────────────────
    public int    $bestK           = 0;
    public float  $silhouetteScore = 0.0;
    public int    $totalMenu       = 0;
    public string $dateFrom        = '';   // actual min date in returned data
    public string $dateTo          = '';   // actual max date in returned data

    public array  $preprocessLogs  = [];
    public array  $tableRows       = [];   // LAPORAN HASIL CLUSTERING
    public array  $kategoriRows    = [];   // LAPORAN KATEGORISASI
    public array  $clusterSummary  = [];   // RATA-RATA per klaster

    // ── Grafik (base64 PNG) ────────────────────────────────────────────────
    public ?string $chartBarJumlah     = null;
    public ?string $chartBarKeuntungan = null;
    public ?string $chartKategorisasi  = null;
    public ?string $chartElbow         = null;
    public ?string $chartSilhouette    = null;

    public function getView(): string
    {
        return 'filament.pages.klasterisasi-menu';
    }

    public function getTitle(): string
    {
        return 'Klasterisasi Menu Penjualan';
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

    // ── Validasi batas kategorisasi ────────────────────────────────────────
    public function isCategoryValid(): bool
    {
        return $this->sangat_laris_batas > $this->laris_batas_bawah
            && $this->laris_batas_bawah > $this->cukup_batas_bawah
            && $this->cukup_batas_bawah > 0;
    }

    // ── Tentukan kategori berdasarkan total jumlah penjualan ───────────────
    private function assignKategori(float $totalJumlah): string
    {
        $total = (int) floor($totalJumlah);
        if ($total > $this->sangat_laris_batas) return 'Sangat Laris';
        if ($total >= $this->laris_batas_bawah)  return 'Laris';
        if ($total >= $this->cukup_batas_bawah)  return 'Cukup';
        return 'Kurang Laris';
    }

    // ── Panggil FastAPI dan simpan hasil ───────────────────────────────────
    public function runClustering(): void
    {
        // Validasi tanggal
        if (! $this->isDatesValid()) {
            Notification::make()
                ->title('Rentang tanggal belum valid')
                ->body('Isi "Dari Tanggal" dan "Sampai Tanggal" dengan rentang minimal 3 bulan.')
                ->warning()
                ->send();
            return;
        }

        // Validasi batas kategorisasi
        if (! $this->isCategoryValid()) {
            Notification::make()
                ->title('Batas kategorisasi tidak valid')
                ->body('Pastikan: Batas Sangat Laris > Batas Bawah Laris > Batas Bawah Cukup > 0.')
                ->warning()
                ->send();
            return;
        }

        $this->isRunning = true;
        $this->errorMsg  = null;
        $this->hasResult = false;

        try {
            app(\App\Services\DataMiningRunner::class)->dispatch('clustering', $this->inputDateFrom, $this->inputDateTo);

            Notification::make()
                ->title('Clustering sedang diproses')
                ->body('Hasil akan muncul otomatis setelah selesai diproses.')
                ->info()
                ->send();
        } catch (\Throwable $e) {
            report($e);
            $this->errorMsg = $e->getMessage();

            Notification::make()
                ->title('Clustering gagal dimulai')
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
        $run = DataminingRun::latest('clustering');

        if (! $run) {
            return;
        }

        if ($run->status === 'completed') {
            $this->hydrateResult($run->payload ?? []);
            $this->lastRunAt    = $run->created_at?->locale('id')->translatedFormat('d M Y, H:i');
            $this->usedDateFrom = $run->parameters['date_from'] ?? '';
            $this->usedDateTo   = $run->parameters['date_to'] ?? '';
            $this->isRunning    = false;
        } elseif ($run->status === 'failed') {
            $this->errorMsg  = $run->error;
            $this->hasResult = false;
            $this->isRunning = false;
        } else {
            $this->isRunning = true;
        }
    }

    protected function hydrateResult(array $data): void
    {
        $this->bestK           = $data['best_k']           ?? 0;
        $this->silhouetteScore = $data['silhouette_score'] ?? 0.0;
        $this->totalMenu       = $data['total_menu']       ?? 0;
        $this->dateFrom        = $data['date_range']['from'] ?? '';
        $this->dateTo          = $data['date_range']['to']   ?? '';
        $this->preprocessLogs  = $data['preprocessing_logs'] ?? [];
        $this->tableRows       = $data['table_rows']        ?? [];
        $this->clusterSummary  = $data['cluster_summary']   ?? [];

        $this->kategoriRows = array_map(
            fn ($row) => array_merge($row, [
                'Kategori' => $this->assignKategori((float) ($row['Total_Jumlah'] ?? 0)),
            ]),
            $this->tableRows
        );

        $charts = $data['charts'] ?? [];
        $this->chartBarJumlah     = $charts['bar_jumlah']     ?? null;
        $this->chartBarKeuntungan = $charts['bar_keuntungan'] ?? null;
        $this->chartKategorisasi  = $charts['kategorisasi']   ?? null;
        $this->chartElbow         = $charts['elbow']          ?? null;
        $this->chartSilhouette    = $charts['silhouette']     ?? null;

        $this->hasResult = true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run_clustering')
                ->label('Jalankan Clustering')
                ->icon('heroicon-o-cpu-chip')
                ->color('primary')
                ->action(fn () => $this->runClustering()),
        ];
    }
}
