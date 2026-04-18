<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class KlasterisasiMenu extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Klasterisasi Menu Penjualan';

    protected static ?string $title = 'Klasterisasi Menu Penjualan';

    protected static ?int $navigationSort = 11;

    // ── State ──────────────────────────────────────────────────────────
    public bool    $isRunning  = false;
    public bool    $hasResult  = false;
    public ?string $lastRunAt  = null;
    public ?string $errorMsg   = null;

    // ── Hasil clustering ───────────────────────────────────────────────
    public int     $bestK           = 0;
    public float   $silhouetteScore = 0.0;
    public int     $totalMenu       = 0;
    public string  $dateFrom        = '';
    public string  $dateTo          = '';
    public array   $clusters        = [];
    public array   $preprocessLogs  = [];
    public array   $tableRows       = [];   // df_result persis dari notebook

    // ── Grafik (base64 PNG) ────────────────────────────────────────────
    public ?string $chartBar        = null;
    public ?string $chartElbow      = null;
    public ?string $chartSilhouette = null;

    public function getView(): string
    {
        return 'filament.pages.klasterisasi-menu';
    }

    public function getTitle(): string
    {
        return 'Klasterisasi Menu Penjualan';
    }

    // ── Panggil FastAPI ────────────────────────────────────────────────
    public function runClustering(): void
    {
        $this->isRunning = true;
        $this->errorMsg  = null;

        try {
            $response = Http::timeout(120)->post('http://127.0.0.1:8001/clustering');

            if (! $response->successful()) {
                throw new \Exception('FastAPI merespons dengan status ' . $response->status());
            }

            $data = $response->json();

            if (($data['status'] ?? '') === 'error') {
                throw new \Exception($data['message'] ?? 'Unknown error dari FastAPI');
            }

            // Simpan hasil ke state Livewire
            $this->bestK           = $data['best_k']           ?? 0;
            $this->silhouetteScore = $data['silhouette_score'] ?? 0.0;
            $this->totalMenu       = $data['total_menu']       ?? 0;
            $this->dateFrom        = $data['date_range']['from'] ?? '';
            $this->dateTo          = $data['date_range']['to']   ?? '';
            $this->clusters        = $data['clusters']           ?? [];
            $this->preprocessLogs  = $data['preprocessing_logs'] ?? [];
            $this->tableRows       = $data['table_rows']          ?? [];
            $this->chartBar        = $data['charts']['bar']        ?? null;
            $this->chartElbow      = $data['charts']['elbow']      ?? null;
            $this->chartSilhouette = $data['charts']['silhouette']  ?? null;

            $this->hasResult = true;
            $this->lastRunAt = now()->locale('id')->translatedFormat('d M Y, H:i');

            Notification::make()
                ->title('Clustering selesai!')
                ->body("K-Means berhasil. K optimal = {$this->bestK}, Silhouette = {$this->silhouetteScore}")
                ->success()
                ->send();

        } catch (\Exception $e) {
            $this->errorMsg  = $e->getMessage();
            $this->hasResult = false;

            Notification::make()
                ->title('Clustering gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->isRunning = false;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run_clustering')
                ->label('Jalankan Clustering')
                ->icon('heroicon-o-cpu-chip')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Jalankan Proses K-Means Clustering')
                ->modalDescription('Proses ini akan membaca data Riwayat Pesanan Kasir, menjalankan preprocessing (missing value, duplikat, outlier), lalu mengklasterisasi tiap menu berdasarkan total penjualannya. Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Jalankan')
                ->action(fn() => $this->runClustering()),
        ];
    }
}
