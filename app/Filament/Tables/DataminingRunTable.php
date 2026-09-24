<?php

namespace App\Filament\Tables;

use App\Models\DataminingRun;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DataminingRunTable
{
    public static function configure(
        Table $table,
        string $resource,
        string $countLabel,
        string $countKey,
    ): Table {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu Run')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
                TextColumn::make('rentang')
                    ->label('Rentang Data')
                    ->state(fn (DataminingRun $record): string => self::range($record)),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'completed' => 'success',
                        'failed' => 'danger',
                        'running' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'completed' => 'Selesai',
                        'failed' => 'Gagal',
                        'running' => 'Diproses',
                        default => (string) $state,
                    }),
                TextColumn::make('jumlah')
                    ->label($countLabel)
                    ->state(fn (DataminingRun $record): int => count($record->payload[$countKey] ?? [])),
                TextColumn::make('duration')
                    ->label('Durasi')
                    ->state(fn (DataminingRun $record): string => self::duration($record)),
                TextColumn::make('error')
                    ->label('Error')
                    ->state(fn (DataminingRun $record): string => $record->error ?? '-')
                    ->limit(40)
                    ->tooltip(fn (DataminingRun $record): ?string => $record->error)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('detail')
                    ->label('Detail')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (DataminingRun $record): string => $resource::getUrl('view', ['record' => $record])),
                DeleteAction::make()->label('Hapus'),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->poll('5s')
            ->defaultSort('created_at', 'desc');
    }

    private static function range(DataminingRun $record): string
    {
        $from = $record->parameters['date_from'] ?? null;
        $to = $record->parameters['date_to'] ?? null;

        if (! $from && ! $to) {
            return '-';
        }

        return ($from ?? '-') . ' s/d ' . ($to ?? '-');
    }

    private static function duration(DataminingRun $record): string
    {
        if ($record->status === 'running' || ! $record->updated_at) {
            return '-';
        }

        return max(0, $record->created_at->diffInSeconds($record->updated_at)) . ' s';
    }
}
