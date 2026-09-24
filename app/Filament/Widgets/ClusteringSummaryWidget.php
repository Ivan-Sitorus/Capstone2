<?php

namespace App\Filament\Widgets;

use App\Filament\Tables\Components\ColumnInfoTooltip;
use App\Models\DataminingRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ClusteringSummaryWidget extends TableWidget
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
            ->heading('Hasil Clustering Menu')
            ->records(function () {
                $run = DataminingRun::find($this->runId) ?? DataminingRun::latestCompleted('clustering');
                $rows = $run?->payload['table_rows'] ?? [];

                return collect($rows)
                    ->map(fn (array $row, int $i): array => ['__key' => $i] + $row)
                    ->all();
            })
            ->columns([
                TextColumn::make('Nama Item')->label('Menu')->searchable(),
                TextColumn::make('Klaster')->label(ColumnInfoTooltip::label('Klaster', 'Kelompok hasil K-Means berdasarkan total penjualan & keuntungan.'))->badge()->color('primary'),
                TextColumn::make('Total_Jumlah')->label(ColumnInfoTooltip::label('Total Jumlah', 'Total unit terjual selama periode data.'))->numeric(),
                TextColumn::make('Total_Keuntungan')->label(ColumnInfoTooltip::label('Total Keuntungan', 'Total keuntungan (Rp) selama periode data.'))->formatStateUsing(fn ($state) => 'Rp'.number_format((float) $state, 0, ',', '.')),
            ]);
    }
}
