<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ClusteringSummaryWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getPollingInterval(): ?string
    {
        return '5s';
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Hasil Clustering Menu')
            ->records(function () {
                $run = DataminingRun::latestCompleted('clustering');
                $rows = $run?->payload['table_rows'] ?? [];

                return collect($rows)
                    ->map(fn (array $row, int $i): array => ['__key' => $i] + $row)
                    ->all();
            })
            ->columns([
                TextColumn::make('Nama Item')->label('Menu')->searchable(),
                TextColumn::make('Klaster')->label('Klaster')->badge()->color('primary'),
                TextColumn::make('Total_Jumlah')->label('Total Jumlah')->numeric(),
                TextColumn::make('Total_Keuntungan')->label('Total Keuntungan')->money('IDR'),
            ]);
    }
}
