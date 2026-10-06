<?php

namespace App\Filament\Widgets;

use App\Filament\Tables\Components\ColumnInfoTooltip;
use App\Models\DataminingRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PredictionSummaryWidget extends TableWidget
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
            ->heading('Ringkasan Prediksi per Menu')
            ->records(function () {
                $run = DataminingRun::find($this->runId) ?? DataminingRun::latestCompleted('prediction');
                $summary = $run?->payload['summary_table'] ?? [];

                return collect($summary)
                    ->map(fn (array $row, int $i): array => ['__key' => $i] + $row)
                    ->all();
            })
            ->columns([
                TextColumn::make('nama_menu')->label('Menu')->searchable(),
                TextColumn::make('total_forecast')->label(ColumnInfoTooltip::label('Total Forecast', 'Jumlah prediksi penjualan selama 2 hari ke depan.'))->numeric(),
                TextColumn::make('avg_per_day')->label(ColumnInfoTooltip::label('Rata-rata/hari', 'Rata-rata prediksi penjualan per hari.'))->numeric(),
                TextColumn::make('mae')->label(ColumnInfoTooltip::label('MAE', 'Mean Absolute Error: rata-rata selisih absolut prediksi vs aktual. Makin kecil makin baik.'))->numeric(),
                TextColumn::make('rmse')->label(ColumnInfoTooltip::label('RMSE', 'Root Mean Squared Error: seperti MAE tetapi menghukum error besar lebih kuat. Makin kecil makin baik.'))->numeric(),
                TextColumn::make('mape')->label(ColumnInfoTooltip::label('MAPE', 'Mean Absolute Percentage Error (%): rata-rata error relatif. Makin kecil makin baik.'))->numeric(),
                TextColumn::make('smape')->label(ColumnInfoTooltip::label('SMAPE', 'Symmetric MAPE (%): versi MAPE yang lebih stabil. Makin kecil makin baik.'))->numeric(),
                TextColumn::make('model')->label('Model'),
            ]);
    }
}
