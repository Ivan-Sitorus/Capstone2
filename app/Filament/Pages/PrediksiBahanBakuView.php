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

class PrediksiBahanBakuView extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = null;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'ingredient-prediction/{record}';

    public DataMiningRun $record;

    public array $filters = ['record_id' => null];

    public function mount(string $record): void
    {
        $this->record = DataMiningRun::findOrFail($record);
        $this->filters['record_id'] = $this->record->id;
    }

    public function getView(): string
    {
        return 'filament.pages.prediksi-bahan-baku-view';
    }

    public function getTitle(): string
    {
        return 'Detail Prediksi Bahan Baku';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\IngredientForecastAllChart::class,
            \App\Filament\Widgets\FeatureImportanceChart::class,
            \App\Filament\Widgets\EvalMaeChart::class,
            \App\Filament\Widgets\EvalRmseChart::class,
            \App\Filament\Widgets\EvalMapeChart::class,
            \App\Filament\Widgets\EvalSmapeChart::class,
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
                    Tab::make('Hasil Prediksi')
                        ->schema([
                            Section::make('Ringkasan')
                                ->schema([
                                    Grid::make(3)->schema([
                                        TextEntry::make('total_ingredients')
                                            ->label('Bahan Baku Dianalisis')
                                            ->state($r['total_ingredients'] ?? 0),
                                        TextEntry::make('forecast_days')
                                            ->label('Horizon Prediksi')
                                            ->state(($r['forecast_days'] ?? 0) . ' hari'),
                                        TextEntry::make('date_range')
                                            ->label('Periode Data')
                                            ->state(
                                                ($r['date_range']['from'] ?? '-')
                                                . ' s/d '
                                                . ($r['date_range']['to'] ?? '-')
                                            ),
                                    ]),
                                ]),
                            Section::make('Evaluasi Model per Bahan Baku')
                                ->schema([
                                    RepeatableEntry::make('predictions')
                                        ->label('')
                                        ->state(fn (): array => $r['predictions'] ?? [])
                                        ->schema([
                                            TextEntry::make('nama_bahan_baku')->label('Bahan Baku'),
                                            TextEntry::make('satuan')->label('Satuan'),
                                            TextEntry::make('mae')->label('MAE'),
                                            TextEntry::make('rmse')->label('RMSE'),
                                            TextEntry::make('mape')->label('MAPE (%)'),
                                            TextEntry::make('smape')->label('SMAPE (%)'),
                                            TextEntry::make('model')->label('Model'),
                                        ])
                                        ->columns(4),
                                ]),
                            Section::make('Ringkasan Forecast')
                                ->schema(function () use ($r) {
                                    $rows = $r['summary_table'] ?? [];
                                    $entries = [];
                                    foreach ($rows as $i => $row) {
                                        $entries[] = TextEntry::make("summary_{$i}")
                                            ->label($row['nama_bahan_baku'] ?? "Item {$i}")
                                            ->state(
                                                'Total: ' . number_format($row['total_forecast'] ?? 0, 1)
                                                . ' ' . ($row['satuan'] ?? '')
                                                . ' — Rata-rata/hari: ' . number_format($row['avg_per_day'] ?? 0, 1)
                                                . ' — MAPE: ' . number_format($row['mape'] ?? 0, 2) . '%'
                                            );
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
