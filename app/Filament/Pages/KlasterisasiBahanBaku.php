<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class KlasterisasiBahanBaku extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Klasterisasi Bahan Baku';

    protected static ?string $title = 'Klasterisasi Bahan Baku';

    protected static ?int $navigationSort = 13;

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
    public array  $preprocessLogs     = [];

    // ── Grafik (base64 PNG) ────────────────────────────────────────────
    public ?string $chartBar        = null;
    public ?string $chartElbow      = null;
    public ?string $chartSilhouette = null;

    public function getView(): string
    {
        return 'filament.pages.klasterisasi-bahan-baku';
    }

    public function getTitle(): string
    {
        return 'Klasterisasi Bahan Baku';
    }

    // ── Panggil FastAPI endpoint clustering bahan baku ─────────────────
    public function runClustering(): void
    {
        $this->errorMsg = null;

        try {
            $response = Http::timeout(120)->post('http://127.0.0.1:8001/clustering-bahan-baku');

            if (! $response->successful()) {
                throw new \Exception('FastAPI merespons dengan status ' . $response->status());
            }

            $data = $response->json();

            if (($data['status'] ?? '') === 'error') {
                throw new \Exception($data['message'] ?? 'Unknown error dari FastAPI');
            }

            $this->bestK            = $data['best_k']              ?? 0;
            $this->silhouetteScore  = $data['silhouette_score']    ?? 0.0;
            $this->totalIngredients = $data['total_ingredients']   ?? 0;
            $this->dateFrom         = $data['date_range']['from']  ?? '';
            $this->dateTo           = $data['date_range']['to']    ?? '';
            $this->clusters         = $data['clusters']            ?? [];
            $this->tableRows        = $data['table_rows']          ?? [];
            $this->preprocessLogs   = $data['preprocessing_logs'] ?? [];
            $this->chartBar         = $data['charts']['bar']        ?? null;
            $this->chartElbow       = $data['charts']['elbow']      ?? null;
            $this->chartSilhouette  = $data['charts']['silhouette'] ?? null;

            $this->hasResult = true;
            $this->lastRunAt = now()->locale('id')->translatedFormat('d M Y, H:i');

            Notification::make()
                ->title('Clustering Bahan Baku selesai!')
                ->body("K-Means berhasil. K optimal = {$this->bestK}, Silhouette = {$this->silhouetteScore}")
                ->success()
                ->send();

        } catch (\Exception $e) {
            $this->errorMsg  = $e->getMessage();
            $this->hasResult = false;

            Notification::make()
                ->title('Clustering Bahan Baku gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run_clustering_bahan_baku')
                ->label('Jalankan Clustering Bahan Baku')
                ->icon('heroicon-o-cpu-chip')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Jalankan Clustering Bahan Baku')
                ->modalDescription('Proses ini akan membaca data pemakaian bahan baku harian, lalu mengklasterisasi tiap bahan baku berdasarkan total penggunaannya menggunakan K-Means. Pastikan FastAPI sudah berjalan. Lanjutkan?')
                ->modalSubmitActionLabel('Ya, Jalankan')
                ->action(fn () => $this->runClustering()),
        ];
    }
}
