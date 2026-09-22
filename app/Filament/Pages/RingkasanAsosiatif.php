<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\DataminingRun;

class RingkasanAsosiatif extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Ringkasan Asosiatif';

    protected static ?string $title = 'Ringkasan Asosiatif';

    protected static ?int $navigationSort = 10;

    // ── State ──────────────────────────────────────────────────────────────
    public bool    $hasResult = false;
    public ?string $errorMsg  = null;

    /** @var array<int, array<string, mixed>>  Hingga 3 hasil association rule terbaru */
    public array $results = [];

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
        $this->loadHistory();
    }

    public function refreshAsosiatifData(): void
    {
        $this->errorMsg = null;
        $count = DataminingRun::query()
            ->where('type', 'association')
            ->where('status', 'completed')
            ->count();

        if ($count === 0) {
            $this->errorMsg  = 'Belum ada hasil association rule. Jalankan proses di halaman Asosiatif Menu terlebih dahulu.';
            $this->hasResult = false;

            Notification::make()
                ->title('Belum ada data')
                ->body('Jalankan proses di halaman Asosiatif Menu dulu.')
                ->warning()
                ->send();
            return;
        }

        $this->loadHistory();

        $latestRunAt = $this->results[0]['last_run_at'] ?? '-';
        Notification::make()
            ->title('Data diperbarui')
            ->body("Menampilkan {$this->getResultCount()} hasil association rule terakhir. Terbaru: {$latestRunAt}")
            ->success()
            ->send();
    }

    public function getResultCount(): int
    {
        return count($this->results);
    }

    private function loadHistory(): void
    {
        $results = DataminingRun::query()
            ->where('type', 'association')
            ->where('status', 'completed')
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (DataminingRun $run) => array_merge(
                $run->payload ?? [],
                [
                    'last_run_at'     => $run->created_at?->locale('id')->translatedFormat('d M Y, H:i'),
                    'input_date_from' => $run->parameters['date_from'] ?? '',
                    'input_date_to'   => $run->parameters['date_to'] ?? '',
                ]
            ))
            ->values()
            ->all();

        $this->results   = $results;
        $this->hasResult = count($results) > 0;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh_asosiatif')
                ->label('Perbarui Data Asosiatif Menu')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->action(fn () => $this->refreshAsosiatifData()),
        ];
    }
}
