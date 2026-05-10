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

class PrediksiMenuView extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = null;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'menu-prediction/{id}';

    public DataMiningRun $record;

    public function mount(string $id): void
    {
        $this->record = DataMiningRun::findOrFail($id);
    }

    public function getView(): string
    {
        return 'filament.pages.prediksi-menu-view';
    }

    public function getTitle(): string
    {
        return 'Detail Prediksi Menu';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ForecastAllChart::make(['recordId' => $this->record->id]),
            \App\Filament\Widgets\FeatureImportanceChart::make(['recordId' => $this->record->id]),
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
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Hasil Prediksi')
                        ->icon(Heroicon::PresentationChartLine)
                        ->schema([
                            Section::make('Ringkasan')
                                ->columns(3)
                                ->schema([
                                    TextEntry::make('total_menu')
                                        ->label('Menu Dianalisis')
                                        ->state($r['total_menu'] ?? 0),
                                    TextEntry::make('forecast_days')
                                        ->label('Hari Prediksi')
                                        ->state($r['forecast_days'] ?? 0),
                                    TextEntry::make('total_forecast')
                                        ->label('Total Forecast (unit)')
                                        ->state(array_sum(array_column($r['predictions'] ?? [], 'total_forecast'))),
                                ]),
                            Section::make('Detail Forecast per Menu')
                                ->description('Prediksi per menu untuk periode forecast')
                                ->collapsible()
                                ->schema(function () use ($r) {
                                    $predictions = $r['predictions'] ?? [];
                                    $entries = [];
                                    foreach ($predictions as $i => $pred) {
                                        $nama = $pred['nama_menu'] ?? "Menu {$i}";
                                        $forecastDays = $pred['forecast'] ?? [];
                                        $entries[] = Section::make($nama)
                                            ->schema([
                                                TextEntry::make("pred_total_{$i}")
                                                    ->label('Total Forecast')
                                                    ->state(($pred['total_forecast'] ?? 0) . ' unit'),
                                                RepeatableEntry::make("pred_detail_{$i}")
                                                    ->label('Forecast Harian')
                                                    ->state(fn (): array => $forecastDays)
                                                    ->schema([
                                                        TextEntry::make('tanggal'),
                                                        TextEntry::make('hari'),
                                                        TextEntry::make('day_type'),
                                                        TextEntry::make('prediksi')->label('Prediksi'),
                                                        TextEntry::make('batas_bawah')->label('Bawah'),
                                                        TextEntry::make('batas_atas')->label('Atas'),
                                                    ])
                                                    ->columns(6),
                                            ]);
                                    }
                                    return $entries;
                                }),
                            Section::make('Summary Table')
                                ->description('Total prediksi per menu (diurutkan tertinggi)')
                                ->collapsible()
                                ->schema([
                                    RepeatableEntry::make('summary_table')
                                        ->hiddenLabel()
                                        ->state(fn (): array => $r['summary_table'] ?? [])
                                        ->schema([
                                            TextEntry::make('nama_menu')->label('Menu'),
                                            TextEntry::make('total_forecast')->label('Total Forecast')->numeric(decimalPlaces: 1),
                                            TextEntry::make('avg_per_day')->label('Rata/Hari')->numeric(decimalPlaces: 2),
                                            TextEntry::make('model')->label('Model'),
                                        ])
                                        ->columns(4),
                                ]),
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
                            Section::make('Evaluasi Model')
                                ->description('Metrik evaluasi per menu pada data test (25%)')
                                ->collapsible()
                                ->schema([
                                    RepeatableEntry::make('evaluation_metrics')
                                        ->hiddenLabel()
                                        ->state(function () use ($r) {
                                            return collect($r['predictions'] ?? [])->map(fn ($p) => [
                                                'nama' => $p['nama_menu'] ?? $p['nama_bahan_baku'] ?? '-',
                                                'mae' => $p['mae'] ?? 0,
                                                'rmse' => $p['rmse'] ?? 0,
                                                'mape' => $p['mape'] ?? 0,
                                                'smape' => $p['smape'] ?? 0,
                                            ])->all();
                                        })
                                        ->schema([
                                            TextEntry::make('nama')->label('Menu'),
                                            TextEntry::make('mae')->label('MAE')->numeric(decimalPlaces: 2),
                                            TextEntry::make('rmse')->label('RMSE')->numeric(decimalPlaces: 2),
                                            TextEntry::make('mape')
                                                ->label('MAPE (%)')
                                                ->numeric(decimalPlaces: 1)
                                                ->suffix('%'),
                                            TextEntry::make('smape')
                                                ->label('SMAPE (%)')
                                                ->numeric(decimalPlaces: 1)
                                                ->suffix('%'),
                                        ])
                                        ->columns(5),
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
