<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\DataminingRun;
use Carbon\Carbon;

class KlasterisasiBahanBaku extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Klasterisasi Bahan Baku';

    protected static ?string $title = 'Klasterisasi Bahan Baku';

    protected static ?int $navigationSort = 13;

    // ── Input rentang tanggal ──────────────────────────────────────────
    public ?string $inputDateFrom  = null;
    public ?string $inputDateTo    = null;
    public ?string $dateRangeError = null;

    // ── State ──────────────────────────────────────────────────────────
    public bool    $hasResult = false;
    public ?string $lastRunAt = null;
    public ?string $errorMsg  = null;

    // ── Hasil clustering ───────────────────────────────────────────────
    public int    $bestK              = 0;
    public float  $silhouetteScore    = 0.0;
    public int    $totalIngredients   = 0;
    public string $dateFrom           = '';
    public string $dateTo             = '';
    public array  $clusters           = [];
    public array  $tableRows          = [];
    public array  $rataRataTable      = [];
    public array  $preprocessLogs     = [];

    // ── Grafik (base64 PNG) ────────────────────────────────────────────
    public ?string $chartRataKlaster = null;
    public ?string $chartBar         = null;
    public ?string $chartElbow       = null;
    public ?string $chartSilhouette  = null;

    public function getView(): string
    {
        return 'filament.pages.klasterisasi-bahan-baku';
    }

    public function getTitle(): string
    {
        return 'Klasterisasi Bahan Baku';
    }

    // ── Validasi rentang tanggal (minimal 3 bulan) ─────────────────────
    public function isDateRangeValid(): bool
    {
        if (! $this->inputDateFrom || ! $this->inputDateTo) {
            return false;
        }
        try {
            $from = Carbon::parse($this->inputDateFrom);
            $to   = Carbon::parse($this->inputDateTo);
            return $from->lte($to) && $from->diffInMonths($to) >= 3;
        } catch (\Exception) {
            return false;
        }
    }

    // ── Watcher: perbarui pesan error saat tanggal berubah ────────────
    public function updatedInputDateFrom(): void { $this->validateDateRange(); }
    public function updatedInputDateTo(): void   { $this->validateDateRange(); }

    private function validateDateRange(): void
    {
        $this->dateRangeError = null;
        if (! $this->inputDateFrom || ! $this->inputDateTo) return;

        try {
            $from   = Carbon::parse($this->inputDateFrom);
            $to     = Carbon::parse($this->inputDateTo);
            $months = $from->diffInMonths($to);

            if ($from->gt($to)) {
                $this->dateRangeError = 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.';
            } elseif ($months < 3) {
                $kurang = 3 - $months;
                $this->dateRangeError = "Rentang tanggal terlalu pendek ({$months} bulan). Minimal 3 bulan (tambah sekitar {$kurang} bulan lagi).";
            }
        } catch (\Exception) {
            $this->dateRangeError = 'Format tanggal tidak valid.';
        }
    }

    // ── Panggil FastAPI endpoint clustering bahan baku ─────────────────
    public function runClustering(): void
    {
        $this->errorMsg = null;

        if (! $this->isDateRangeValid()) {
            Notification::make()
                ->title('Rentang tanggal tidak valid')
                ->body('Pilih rentang tanggal data penggunaan bahan baku minimal 3 bulan.')
                ->warning()
                ->send();
            return;
        }

        $this->errorMsg  = null;
        $this->hasResult = false;

        try {
            app(\App\Services\DataMiningRunner::class)->dispatch('clustering-bahan-baku', $this->inputDateFrom, $this->inputDateTo);

            Notification::make()
                ->title('Clustering Bahan Baku sedang diproses')
                ->body('Hasil akan muncul otomatis setelah selesai diproses.')
                ->info()
                ->send();
        } catch (\Throwable $e) {
            report($e);
            $this->errorMsg = $e->getMessage();

            Notification::make()
                ->title('Clustering Bahan Baku gagal dimulai')
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
        $run = DataminingRun::latest('clustering-bahan-baku');

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
        $this->bestK            = $data['best_k']             ?? 0;
        $this->silhouetteScore  = $data['silhouette_score']   ?? 0.0;
        $this->totalIngredients = $data['total_ingredients']  ?? 0;
        $this->dateFrom         = $data['date_range']['from'] ?? '';
        $this->dateTo           = $data['date_range']['to']   ?? '';
        $this->clusters         = $data['clusters']           ?? [];
        $this->tableRows        = $data['table_rows']         ?? [];
        $this->rataRataTable    = $data['rata_rata_table']    ?? [];
        $this->preprocessLogs   = $data['preprocessing_logs'] ?? [];

        $charts = $data['charts'] ?? [];
        $this->chartRataKlaster = $charts['rata_klaster'] ?? null;
        $this->chartBar         = $charts['bar']          ?? null;
        $this->chartElbow       = $charts['elbow']        ?? null;
        $this->chartSilhouette  = $charts['silhouette']   ?? null;

        $this->hasResult = true;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run_clustering_bahan_baku')
                ->label('Jalankan Clustering Bahan Baku')
                ->icon('heroicon-o-cpu-chip')
                ->color('primary')
                ->disabled(fn () => ! $this->isDateRangeValid())
                ->requiresConfirmation()
                ->modalHeading('Jalankan Clustering Bahan Baku')
                ->modalDescription('Proses ini akan membaca data pemakaian bahan baku harian sesuai rentang tanggal yang dipilih, lalu mengklasterisasi tiap bahan baku berdasarkan total penggunaannya menggunakan K-Means. Pastikan FastAPI sudah berjalan. Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Jalankan')
                ->action(fn () => $this->runClustering()),
        ];
    }
}
