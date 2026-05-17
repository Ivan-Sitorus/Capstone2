<?php

namespace App\Filament\Resources\MenuStockResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenuStockBatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'batches';

    protected static ?string $title = 'Batch Stok';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        $unit = $this->ownerRecord->unit ?? '';

        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Batch ID')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Quantity')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' '.$unit)
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->label('Expiry Date')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => ! $record->expiry_date ? 'gray' : ($record->expiry_date->isPast() ? 'danger' : ($record->expiry_date->diffInDays(now()) < 7 ? 'warning' : 'success'))),
                TextColumn::make('received_at')
                    ->label('Received At')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('cost_per_unit')
                    ->label('Cost/Unit')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('expiry_date', 'asc');
    }
}
