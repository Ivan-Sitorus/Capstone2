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
    public function getView(): string { return 'filament.pages.prediksi-menu-view'; }
    public function getTitle(): string { return 'Detail Prediksi Menu'; }
    protected function getHeaderWidgets(): array {
        return [
            \App\Filament\Widgets\ForecastAllChart::make(['recordId' => $this->record->id]),
            \App\Filament\Widgets\FeatureImportanceChart::make(['recordId' => $this->record->id]),
        ];
    }
    public function getHeaderWidgetsColumns(): int | array { return 2; }

    // COMBO-4: ALL sections, minimal closures
    public function content(Schema $schema): Schema
    {
        $r = $this->record->result ?? [];
        $predictions = $r['predictions'] ?? [];

        return $schema->components([
            Tabs::make('Detail')->columnSpanFull()->tabs([
                Tab::make('Hasil Prediksi')->icon(Heroicon::PresentationChartLine)->schema([
                    Section::make('Sekilas')->columns(4)->schema([
                        TextEntry::make('total_menu')->label('Menu')->state($r['total_menu'] ?? 0),
                        TextEntry::make('forecast_days')->label('Hari')->state($r['forecast_days'] ?? 0),
                        TextEntry::make('total_forecast')->label('Total (unit)')->state(array_sum(array_column($predictions, 'total_forecast'))),
                        TextEntry::make('avg_mape')->label('MAPE')->state(function () use ($r) {
                            $m = array_column($r['predictions'] ?? [], 'mape');
                            return count($m) > 0 ? round(array_sum($m) / count($m), 1) . '%' : 'N/A';
                        }),
                    ]),
                    // Ranking
                    Section::make('Ranking Prediksi Menu')->schema([
                        RepeatableEntry::make('summary_table')->hiddenLabel()
                            ->state(fn (): array => $r['summary_table'] ?? [])
                            ->schema([
                                TextEntry::make('nama_menu')->label('Menu'),
                                TextEntry::make('total_forecast')->label('Total')->numeric(0)->suffix(' unit'),
                                TextEntry::make('mape')->label('MAPE')->numeric(1)->suffix('%'),
                            ])->columns(3),
                    ]),
                    Section::make('Detail Forecast Harian')->collapsible()
                        ->schema(function () use ($predictions) {
                            $entries = [];
                            foreach ($predictions as $i => $pred) {
                                $nama = $pred['nama_menu'] ?? "Menu {$i}";
                                $forecastDays = $pred['forecast'] ?? [];
                                $entries[] = Section::make($nama)->schema([
                                    TextEntry::make("pt_{$i}")->label('Total')->state(($pred['total_forecast'] ?? 0) . ' unit'),
                                    RepeatableEntry::make("pd_{$i}")->label('Harian')
                                        ->state(fn (): array => $forecastDays)
                                        ->schema([
                                            TextEntry::make('tanggal'), TextEntry::make('hari'),
                                            TextEntry::make('day_type')->label('Tipe')->badge()
                                                ->color(fn (string $state): string => $state === 'Weekend' ? 'warning' : 'info'),
                                            TextEntry::make('prediksi')->badge()->color('primary'),
                                            TextEntry::make('batas_bawah')->label('Bawah'),
                                            TextEntry::make('batas_atas')->label('Atas'),
                                        ])->columns(6),
                                ]);
                            }
                            return $entries;
                        }),
                ]),
                Tab::make('Detail Teknis')->icon(Heroicon::Cog)->schema([
                    Section::make('Parameter')->columns(2)->schema([
                        TextEntry::make('type')->state($this->record->analysis_type),
                        TextEntry::make('status')->state($this->record->status),
                        TextEntry::make('run_at')->state($this->record->run_at?->format('d M Y, H:i')),
                        TextEntry::make('dr')->state(($r['date_range']['from']??'?').' - '.($r['date_range']['to']??'?')),
                        TextEntry::make('fr')->label('Prediksi')->state(($r['forecast_range']['from']??'?').' - '.($r['forecast_range']['to']??'?')),
                    ]),
                    Section::make('Evaluasi Model')->collapsed()->schema([
                        RepeatableEntry::make('eval')->hiddenLabel()
                            ->state(function () use ($r) {
                                return collect($r['predictions'] ?? [])->map(fn ($p) => [
                                    'nama' => $p['nama_menu'] ?? '-', 'mae' => $p['mae'] ?? 0,
                                    'rmse' => $p['rmse'] ?? 0, 'mape' => $p['mape'] ?? 0, 'smape' => $p['smape'] ?? 0,
                                ])->all();
                            })
                            ->schema([
                                TextEntry::make('nama'), TextEntry::make('mae')->numeric(2),
                                TextEntry::make('rmse')->numeric(2),
                                TextEntry::make('mape')->numeric(1)->suffix('%')
                                    ->color(fn ($state): string => is_numeric($state) ? ($state <= 20 ? 'success' : ($state <= 50 ? 'warning' : 'danger')) : 'gray'),
                                TextEntry::make('smape')->numeric(1)->suffix('%'),
                            ])->columns(5),
                    ]),
                    Section::make('Preprocessing Logs')->collapsed()
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
