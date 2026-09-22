<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class ClusteringHistoryWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getPollingInterval(): ?string
    {
        return '5s';
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Riwayat Klasterisasi Menu')
            ->records(function () {
                return DataminingRun::completedHistory('clustering', 100)
                    ->map(fn (DataminingRun $run, int $i): array => [
                        '__key'            => $i,
                        'waktu'            => $run->created_at?->locale('id')->translatedFormat('d M Y, H:i'),
                        'dari'             => $run->parameters['date_from'] ?? '',
                        'sampai'           => $run->parameters['date_to'] ?? '',
                        'best_k'           => $run->payload['best_k'] ?? 0,
                        'silhouette_score' => $run->payload['silhouette_score'] ?? 0.0,
                        'total_menu'       => $run->payload['total_menu'] ?? 0,
                    ])
                    ->all();
            })
            ->columns([
                TextColumn::make('waktu')->label('Waktu'),
                TextColumn::make('dari')->label('Dari')->date('d M Y'),
                TextColumn::make('sampai')->label('Sampai')->date('d M Y'),
                TextColumn::make('best_k')->label('K Optimal')->numeric(),
                TextColumn::make('silhouette_score')->label('Silhouette')->numeric(decimalPlaces: 4),
                TextColumn::make('total_menu')->label('Total Menu')->numeric(),
            ]);
    }
}
