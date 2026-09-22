<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class AssociationHistoryWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getPollingInterval(): ?string
    {
        return '5s';
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Riwayat Asosiatif Menu')
            ->records(function () {
                return DataminingRun::completedHistory('association', 100)
                    ->map(fn (DataminingRun $run, int $i): array => [
                        '__key'              => $i,
                        'waktu'              => $run->created_at?->locale('id')->translatedFormat('d M Y, H:i'),
                        'dari'               => $run->parameters['date_from'] ?? '',
                        'sampai'             => $run->parameters['date_to'] ?? '',
                        'total_rules'        => $run->payload['total_rules'] ?? 0,
                        'total_transactions' => $run->payload['total_transactions'] ?? 0,
                    ])
                    ->all();
            })
            ->columns([
                TextColumn::make('waktu')->label('Waktu'),
                TextColumn::make('dari')->label('Dari')->date('d M Y'),
                TextColumn::make('sampai')->label('Sampai')->date('d M Y'),
                TextColumn::make('total_rules')->label('Total Rules')->numeric(),
                TextColumn::make('total_transactions')->label('Total Transaksi')->numeric(),
            ]);
    }
}
