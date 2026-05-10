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
                    // ── TAB: HASIL PREDIKSI (business-facing) ──────────
                    Tab::make('Hasil Prediksi')
                        ->icon(Heroicon::PresentationChartLine)
                        ->schema([
                            // STAT CARDS: 3 key metrics at a glance
                            Section::make('Sekilas')
                                ->columns(4)
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
                                    TextEntry::make('avg_mape')
                                        ->label('Rata-rata MAPE')
                                        ->state(function () use ($r) {
                                            $mapes = array_column($r['predictions'] ?? [], 'mape');
                                            return count($mapes) > 0
                                                ? round(array_sum($mapes) / count($mapes), 1) . '%'
                                                : 'N/A';
                                        }),
                                ]),

                            // RANKING TABLE: top menus by predicted sales
                            Section::make('Ranking Prediksi Menu')
                                ->description('Diurutkan dari total prediksi tertinggi. Gunakan untuk perencanaan stok dan persiapan.')
                                ->schema([
                                    RepeatableEntry::make('summary_table')
                                        ->hiddenLabel()
                                        ->state(fn (): array => $r['summary_table'] ?? [])
                                        ->schema([
                                            TextEntry::make('nama_menu')->label('Menu'),
                                            TextEntry::make('total_forecast')
                                                ->label('Total Prediksi')
                                                ->numeric(decimalPlaces: 0)
                                                ->suffix(' unit'),
                                            TextEntry::make('mape')
                                                ->label('MAPE')
                                                ->state(fn (array $state): string => number_format($state['mape'] ?? 0, 1) . '%')
                                                ->color(fn (array $state): string =>
                                                    ($state['mape'] ?? 100) <= 20 ? 'success' :
                                                    (($state['mape'] ?? 100) <= 50 ? 'warning' : 'danger')
                                                ),
                                        ])
                                        ->columns(3),
                                ]),

                            // DETAIL FORECAST: per-menu daily breakdown
                            Section::make('Detail Forecast Harian')
                                ->description('Prediksi per menu untuk 2 hari ke depan')
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
                                                    ->label('Total')
                                                    ->state(($pred['total_forecast'] ?? 0) . ' unit'),
                                                RepeatableEntry::make("pred_detail_{$i}")
                                                    ->label('Rincian Harian')
                                                    ->state(fn (): array => $forecastDays)
                                                    ->schema([
                                                        TextEntry::make('tanggal'),
                                                        TextEntry::make('hari'),
                                                        TextEntry::make('day_type')
                                                            ->label('Tipe')
                                                            ->badge()
                                                            ->color(fn (string $state): string =>
                                                                $state === 'Weekend' ? 'warning' : 'info'
                                                            ),
                                                        TextEntry::make('prediksi')
                                                            ->label('Prediksi')
                                                            ->badge()
                                                            ->color('primary'),
                                                        TextEntry::make('batas_bawah')->label('Batas Bawah'),
                                                        TextEntry::make('batas_atas')->label('Batas Atas'),
                                                    ])
                                                    ->columns(6),
                                            ]);
                                    }
                                    return $entries;
                                }),
                        ]),

                    // ── TAB: DETAIL TEKNIS (data scientist) ────────────
                    Tab::make('Detail Teknis')
                        ->icon(Heroicon::Cog)
                        ->schema([
                            Section::make('Parameter Model')
                                ->columns(2)
                                ->schema([
                                    TextEntry::make('type')->label('Tipe')->state($this->record->analysis_type),
                                    TextEntry::make('status')->label('Status')->state($this->record->status),
                                    TextEntry::make('run_at')->label('Waktu Eksekusi')->state($this->record->run_at?->format('d M Y, H:i:s')),
                                    TextEntry::make('date_range')->label('Periode Data')
                                        ->state(($r['date_range']['from'] ?? '?') . ' s/d ' . ($r['date_range']['to'] ?? '?')),
                                    TextEntry::make('forecast_range')->label('Periode Prediksi')
                                        ->state(($r['forecast_range']['from'] ?? '?') . ' s/d ' . ($r['forecast_range']['to'] ?? '?')),
                                ]),

                            Section::make('Evaluasi Model')
                                ->description('Metrik error per menu pada data test (25%). MAPE ≤20% = akurat, ≤50% = cukup, >50% = kurang akurat.')
                                ->collapsed()
                                ->schema([
                                    RepeatableEntry::make('evaluation_metrics')
                                        ->hiddenLabel()
                                        ->state(function () use ($r) {
                                            return collect($r['predictions'] ?? [])->map(fn ($p) => [
                                                'nama' => $p['nama_menu'] ?? '-',
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
                                                ->suffix('%')
                                                ->color(fn (array $state): string =>
                                                    ($state['mape'] ?? 100) <= 20 ? 'success' :
                                                    (($state['mape'] ?? 100) <= 50 ? 'warning' : 'danger')
                                                ),
                                            TextEntry::make('smape')
                                                ->label('SMAPE (%)')
                                                ->numeric(decimalPlaces: 1)
                                                ->suffix('%'),
                                        ])
                                        ->columns(5),
                                ]),

                            Section::make('Preprocessing Logs')
                                ->collapsed()
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
