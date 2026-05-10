<?php

namespace App\Filament\Pages;

use App\Models\DataMiningRun;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class KlasterisasiBahanBakuView extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = null;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'ingredient-clustering/{record}';

    public DataMiningRun $record;

    public array $filters = ['record_id' => null];

    public function mount(string $record): void
    {
        $this->record = DataMiningRun::findOrFail($record);
        $this->filters['record_id'] = $this->record->id;
    }

    public function getView(): string
    {
        return 'filament.pages.klasterisasi-bahan-baku-view';
    }

    public function getTitle(): string
    {
        return 'Detail Klasterisasi Bahan Baku';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\IngredientBarChart::class,
            \App\Filament\Widgets\ElbowChart::class,
            \App\Filament\Widgets\SilhouetteChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 2;
    }

    public function content(Schema $schema): Schema
    {
        $r = $this->record->result ?? [];

        return $schema->components([
            Tabs::make('Detail')
                ->tabs([
                    Tab::make('Hasil Klaster')
                        ->schema([
                            Section::make('Ringkasan')
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextEntry::make('best_k')
                                            ->label('K Optimal')
                                            ->state($r['best_k'] ?? '-'),
                                        TextEntry::make('silhouette')
                                            ->label('Silhouette Score')
                                            ->state(round($r['silhouette_score'] ?? 0, 4)),
                                        TextEntry::make('total_ingredients')
                                            ->label('Bahan Baku Dianalisis')
                                            ->state($r['total_ingredients'] ?? 0),
                                    ]),
                                ]),
                            Section::make('Klaster')
                                ->schema([
                                    RepeatableEntry::make('clusters')
                                        ->label('')
                                        ->state(fn (): array => $r['clusters'] ?? [])
                                        ->schema([
                                            TextEntry::make('label'),
                                            TextEntry::make('count')->label('Jumlah Item'),
                                            TextEntry::make('avg_usage')->label('Rata-rata Penggunaan'),
                                            TextEntry::make('total_usage')->label('Total Penggunaan'),
                                        ])
                                        ->columns(4),
                                ]),
                            Section::make('Tabel Hasil')
                                ->schema(function () use ($r) {
                                    $rows = $r['table_rows'] ?? [];
                                    $entries = [];
                                    foreach ($rows as $i => $row) {
                                        $entries[] = TextEntry::make("row_{$i}")
                                            ->label($row['Nama Bahan Baku'] ?? "Item {$i}")
                                            ->state('Klaster ' . ($row['Klaster'] ?? '?') . ' — ' . ($row['Kategori'] ?? ''));
                                    }

                                    return $entries;
                                }),
                        ]),
                    Tab::make('Detail Teknis')
                        ->schema([
                            Section::make('Parameter Model')
                                ->schema([
                                    TextEntry::make('type')->label('Tipe')->state($this->record->analysis_type),
                                    TextEntry::make('status')->label('Status')->state($this->record->status),
                                    TextEntry::make('run_at')->label('Waktu Eksekusi')->state($this->record->run_at?->format('d M Y, H:i:s')),
                                    TextEntry::make('date_range_start')->label('Data Dari')->state($this->record->date_range_start?->format('d M Y')),
                                    TextEntry::make('date_range_end')->label('Data Sampai')->state($this->record->date_range_end?->format('d M Y')),
                                ])
                                ->columns(2),
                            Section::make('Preprocessing Logs')
                                ->schema(function () use ($r) {
                                    $logs = $r['preprocessing_logs'] ?? [];
                                    $entries = [];
                                    foreach ($logs as $i => $log) {
                                        $entries[] = TextEntry::make("log_{$i}")
                                            ->label($log['tahap'] ?? "Step {$i}")
                                            ->state($log['detail'] ?? '');
                                    }

                                    return $entries;
                                }),
                        ]),
                ]),
        ]);
    }
}
