<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PredictionHistoryWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getPollingInterval(): ?string
    {
        return '5s';
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Riwayat Prediksi Menu')
            ->records(function () {
                return DataminingRun::completedHistory('prediction', 10)
                    ->map(fn (DataminingRun $run, int $i): array => [
                        '__key'         => $i,
                        'waktu'         => $run->created_at?->locale('id')->translatedFormat('d M Y, H:i'),
                        'dari'          => $run->parameters['date_from'] ?? '',
                        'sampai'        => $run->parameters['date_to'] ?? '',
                        'total_menu'    => $run->payload['total_menu'] ?? 0,
                        'forecast_days' => $run->payload['forecast_days'] ?? 0,
                    ])
                    ->all();
            })
            ->columns([
                TextColumn::make('waktu')->label('Waktu')->sortable(),
                TextColumn::make('dari')->label('Dari')->date('d M Y'),
                TextColumn::make('sampai')->label('Sampai')->date('d M Y'),
                TextColumn::make('total_menu')->label('Total Menu')->numeric(),
                TextColumn::make('forecast_days')->label('Forecast (hari)')->numeric(),
            ]);
    }
}
