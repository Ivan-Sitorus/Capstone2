<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PredictionsPerDayWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public ?int $runId = null;

    protected function getTableHeading(): ?string
    {
        return 'Prediksi 2 Hari ke Depan';
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(function (): array {
                $run = DataminingRun::find($this->runId);
                $predictions = $run?->payload['predictions'] ?? [];

                $rows = [];
                $key = 0;

                foreach ($predictions as $prediction) {
                    $name = $prediction['nama_menu'] ?? $prediction['nama_bahan_baku'] ?? $prediction['bahan'] ?? '-';

                    foreach (($prediction['forecast'] ?? []) as $day) {
                        $rows[] = [
                            '__key' => $key++,
                            'nama' => $name,
                            'tanggal' => $day['tanggal'] ?? '-',
                            'hari' => $day['hari'] ?? ($day['day_type'] ?? '-'),
                            'prediksi' => $day['prediksi'] ?? 0,
                            'batas_bawah' => $day['batas_bawah'] ?? 0,
                            'batas_atas' => $day['batas_atas'] ?? 0,
                        ];
                    }
                }

                return $rows;
            })
            ->columns([
                TextColumn::make('nama')->label('Menu / Bahan Baku')->searchable(),
                TextColumn::make('tanggal')->label('Tanggal'),
                TextColumn::make('hari')->label('Hari'),
                TextColumn::make('prediksi')->label('Prediksi')->numeric(),
                TextColumn::make('batas_bawah')->label('Batas Bawah')->numeric(),
                TextColumn::make('batas_atas')->label('Batas Atas')->numeric(),
            ])
            ->paginated([5, 10, 25]);
    }
}
