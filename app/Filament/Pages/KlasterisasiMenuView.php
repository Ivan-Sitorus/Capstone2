<?php

namespace App\Filament\Pages;

use App\Models\DataMiningRun;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class KlasterisasiMenuView extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = null;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'menu-clustering/{id}';

    public DataMiningRun $record;

    public function mount(string $id): void
    {
        $this->record = DataMiningRun::findOrFail($id);
    }

    public function getView(): string
    {
        return 'filament.pages.klasterisasi-menu-view';
    }

    public function getTitle(): string
    {
        return 'Detail Klasterisasi Menu';
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 2;
    }

    public function content(Schema $schema): Schema
    {
        $r = $this->record->result ?? [];

        return $schema->components([
            Tabs::make('Detail')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Hasil Clustering')
                        ->icon(Heroicon::Squares2x2)
                        ->schema([
                            Section::make('Ringkasan')
                                ->columns(3)
                                ->schema([
                                    TextEntry::make('best_k')
                                        ->label('K Optimal')
                                        ->state($r['best_k'] ?? '-'),
                                    TextEntry::make('silhouette')
                                        ->label('Silhouette Score')
                                        ->state(round($r['silhouette_score'] ?? 0, 4)),
                                    TextEntry::make('total_menu')
                                        ->label('Menu Dianalisis')
                                        ->state($r['total_menu'] ?? 0),
                                ]),
                            Section::make('Klaster')
                                ->collapsible()
                                ->schema([
                                    RepeatableEntry::make('clusters')
                                        ->hiddenLabel()
                                        ->state(fn (): array => $r['clusters'] ?? [])
                                        ->schema([
                                            TextEntry::make('label'),
                                            TextEntry::make('count')->label('Jumlah Menu'),
                                            TextEntry::make('total_sales')->label('Total Penjualan'),
                                        ])
                                        ->columns(3),
                                ]),
                            Section::make('Tabel Hasil')
                                ->description('Detail menu per klaster')
                                ->collapsible()
                                ->schema(function () use ($r) {
                                    $rows = $r['table_rows'] ?? [];
                                    $entries = [];
                                    foreach ($rows as $i => $row) {
                                        $entries[] = TextEntry::make("row_{$i}")
                                            ->label($row['Nama Item'] ?? "Item {$i}")
                                            ->state('Klaster '.($row['Klaster'] ?? '?').' — '.($row['Kategori'] ?? ''));
                                    }

                                    return $entries;
                                }),
                        ]),
                    Tab::make('Detail Teknis')
                        ->icon(Heroicon::Cog)
                        ->schema([
                            Section::make('Parameter Model')
                                ->columns(2)
                                ->schema([
                                    TextEntry::make('type')->label('Tipe')->state($this->record->analysis_type),
                                    TextEntry::make('status')->label('Status')->state($this->record->status),
                                    TextEntry::make('run_at')->label('Waktu Eksekusi')->state($this->record->run_at?->format('d M Y, H:i:s')),
                                    TextEntry::make('date_range_start')->label('Data Dari')->state($this->record->date_range_start?->format('d M Y')),
                                    TextEntry::make('date_range_end')->label('Data Sampai')->state($this->record->date_range_end?->format('d M Y')),
                                ]),
                            Section::make('Preprocessing Logs')
                                ->collapsible()
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
