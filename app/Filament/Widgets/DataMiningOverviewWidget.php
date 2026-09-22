<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class DataMiningOverviewWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getPollingInterval(): ?string
    {
        return '5s';
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Status Data Mining')
            ->records(function () {
                $types = [
                    'prediction'            => 'Prediksi Menu',
                    'clustering'            => 'Klasterisasi Menu',
                    'association'           => 'Asosiatif Menu',
                    'clustering-bahan-baku' => 'Klasterisasi Bahan Baku',
                    'prediction-bahan-baku' => 'Prediksi Bahan Baku',
                ];

                return collect($types)
                    ->map(function (string $label, string $type) use ($types) {
                        $run = DataminingRun::latest($type);

                        return [
                            'type'   => $label,
                            'status' => $run?->status ?? 'belum ada',
                            'waktu'  => $run?->created_at?->locale('id')->translatedFormat('d M Y, H:i') ?? '-',
                        ];
                    })
                    ->values()
                    ->map(fn (array $row, int $i): array => ['__key' => $i] + $row)
                    ->all();
            })
            ->columns([
                TextColumn::make('type')->label('Jenis Analisis'),
                TextColumn::make('status')->label('Status')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'failed'    => 'danger',
                        'running'   => 'warning',
                        default     => 'gray',
                    }),
                TextColumn::make('waktu')->label('Terakhir Diperbarui'),
            ]);
    }
}
