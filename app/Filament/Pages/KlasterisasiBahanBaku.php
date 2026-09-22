<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ClusteringBahanBakuChartWidget;
use App\Filament\Widgets\ClusteringBahanBakuSummaryWidget;
use App\Services\DataMiningRunner;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class KlasterisasiBahanBaku extends Page
{
    use HasFiltersForm;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Klasterisasi Bahan Baku';

    protected static ?string $title = 'Klasterisasi Bahan Baku';

    protected static ?int $navigationSort = 13;

    public function mount(): void
    {
        $this->mountHasFilters();

        if (empty($this->filters['from'])) {
            $this->filters['from'] = now()->subMonths(3)->toDateString();
        }
        if (empty($this->filters['until'])) {
            $this->filters['until'] = now()->toDateString();
        }
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            DatePicker::make('from')
                ->label('Dari Tanggal')
                ->native(false)
                ->displayFormat('d M Y')
                ->maxDate(now()),
            DatePicker::make('until')
                ->label('Sampai Tanggal')
                ->native(false)
                ->displayFormat('d M Y')
                ->maxDate(now()),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                EmbeddedSchema::make('filtersForm'),
                Grid::make()
                    ->schema(fn (): array => $this->getWidgetsSchemaComponents($this->getWidgets())),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            ClusteringBahanBakuChartWidget::class,
            ClusteringBahanBakuSummaryWidget::class,
        ];
    }

    public function isDateRangeValid(): bool
    {
        $from = $this->filters['from'] ?? null;
        $to   = $this->filters['until'] ?? null;

        if (! $from || ! $to) {
            return false;
        }

        try {
            return Carbon::parse($to)->greaterThan(Carbon::parse($from))
                && Carbon::parse($from)->diffInMonths(Carbon::parse($to)) >= 3;
        } catch (\Throwable) {
            return false;
        }
    }

    public function runClustering(): void
    {
        if (! $this->isDateRangeValid()) {
            Notification::make()
                ->title('Rentang tanggal belum valid')
                ->body('Pilih rentang tanggal minimal 3 bulan.')
                ->warning()
                ->send();

            return;
        }

        try {
            app(DataMiningRunner::class)->dispatch('clustering-bahan-baku', $this->filters['from'], $this->filters['until']);

            Notification::make()
                ->title('Clustering Bahan Baku sedang diproses')
                ->body('Hasil akan muncul otomatis setelah selesai diproses.')
                ->info()
                ->send();
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Clustering Bahan Baku gagal dimulai')
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
                ->disabled(fn () => ! $this->isDateRangeValid())
                ->action(fn () => $this->runClustering()),
        ];
    }
}
