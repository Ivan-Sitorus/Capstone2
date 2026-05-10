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

class AsosiatifMenuView extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = null;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'association/{id}';

    public DataMiningRun $record;

    public function mount(string $id): void
    {
        $this->record = DataMiningRun::findOrFail($id);
    }

    public function getView(): string
    {
        return 'filament.pages.asosiatif-menu-view';
    }

    public function getTitle(): string
    {
        return 'Detail Aturan Asosiasi';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\TopRulesChart::class,
            \App\Filament\Widgets\SupConfChart::class,
            \App\Filament\Widgets\FreqItemChart::class,
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
                    Tab::make('Hasil Asosiasi')
                        ->schema([
                            Section::make('Ringkasan')
                                ->schema([
                                    Grid::make(4)->schema([
                                        TextEntry::make('total_rules')
                                            ->label('Total Rules')
                                            ->state($r['total_rules'] ?? 0),
                                        TextEntry::make('total_transactions')
                                            ->label('Total Transaksi')
                                            ->state($r['total_transactions'] ?? 0),
                                        TextEntry::make('min_support')
                                            ->label('Min Support')
                                            ->state(fn (): string => isset($r['min_support']) ? number_format($r['min_support'] * 100, 1) . '%' : '-'),
                                        TextEntry::make('min_confidence')
                                            ->label('Min Confidence')
                                            ->state(fn (): string => isset($r['min_confidence']) ? number_format($r['min_confidence'] * 100, 1) . '%' : '-'),
                                    ]),
                                ]),
                            Section::make('Aturan Asosiasi')
                                ->schema([
                                    RepeatableEntry::make('rules')
                                        ->label('')
                                        ->state(fn (): array => $r['rules'] ?? [])
                                        ->schema([
                                            TextEntry::make('menu_pertama')->label('Menu Pertama'),
                                            TextEntry::make('menu_kedua')->label('Menu Kedua'),
                                            TextEntry::make('support')
                                                ->label('Support')
                                                ->state(fn (array $state): string => number_format(($state['support'] ?? 0) * 100, 2) . '%'),
                                            TextEntry::make('confidence')
                                                ->label('Confidence')
                                                ->state(fn (array $state): string => number_format(($state['confidence'] ?? 0) * 100, 2) . '%'),
                                            TextEntry::make('lift')
                                                ->label('Lift')
                                                ->state(fn (array $state): string => number_format($state['lift'] ?? 0, 2)),
                                        ])
                                        ->columns(5),
                                ]),
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
                                    TextEntry::make('min_support_param')
                                        ->label('Min Support')
                                        ->state(fn (): string => isset($r['min_support']) ? number_format($r['min_support'] * 100, 1) . '%' : '-'),
                                    TextEntry::make('min_confidence_param')
                                        ->label('Min Confidence')
                                        ->state(fn (): string => isset($r['min_confidence']) ? number_format($r['min_confidence'] * 100, 1) . '%' : '-'),
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
