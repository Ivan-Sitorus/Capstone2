<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PredictionBahanBakuSummaryWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public ?int $runId = null;

    protected function getPollingInterval(): ?string
    {
        return '5s';
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Ringkasan Prediksi per Bahan Baku')
            ->records(function () {
                $run = DataminingRun::find($this->runId) ?? DataminingRun::latestCompleted('prediction-bahan-baku');
                $summary = $run?->payload['summary_table'] ?? [];

                return collect($summary)
                    ->map(fn (array $row, int $i): array => ['__key' => $i] + $row)
                    ->all();
            })
            ->columns([
                TextColumn::make('nama_bahan_baku')->label('Bahan Baku')->searchable(),
                TextColumn::make('satuan')->label('Satuan'),
                TextColumn::make('total_forecast')->label('Total Forecast')->numeric(),
                TextColumn::make('avg_per_day')->label('Rata-rata/hari')->numeric(),
                TextColumn::make('mae')->label('MAE')->numeric(),
                TextColumn::make('rmse')->label('RMSE')->numeric(),
                TextColumn::make('mape')->label('MAPE')->numeric(),
                TextColumn::make('smape')->label('SMAPE')->numeric(),
                TextColumn::make('model')->label('Model'),
            ]);
    }
}
