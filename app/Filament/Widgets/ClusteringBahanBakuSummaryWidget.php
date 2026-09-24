<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ClusteringBahanBakuSummaryWidget extends TableWidget
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
            ->heading('Hasil Clustering Bahan Baku')
            ->records(function () {
                $run = DataminingRun::find($this->runId) ?? DataminingRun::latestCompleted('clustering-bahan-baku');
                $rows = $run?->payload['table_rows'] ?? [];

                return collect($rows)
                    ->map(fn (array $row, int $i): array => ['__key' => $i] + $row)
                    ->all();
            })
            ->columns([
                TextColumn::make('Nama Bahan Baku')->label('Bahan Baku')->searchable(),
                TextColumn::make('Satuan')->label('Satuan'),
                TextColumn::make('Klaster')->label('Klaster')->badge()->color('primary'),
                TextColumn::make('Total Penggunaan')->label('Total Penggunaan')->numeric(),
            ]);
    }
}
