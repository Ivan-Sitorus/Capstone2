<?php

namespace App\Filament\Resources\StockAdjustmentResource\Tables;

use App\Models\StockAdjustment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockAdjustmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['ingredient', 'menu', 'reportedBy']))
            ->columns([
                TextColumn::make('adjusted_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Kode')
                    ->sortable(),
                TextColumn::make('adjustment_type')
                    ->label('Tipe Penyesuaian')
                    ->badge()
                    ->color(fn (string $state): string => $state === StockAdjustment::TYPE_INCREASE ? 'primary' : 'danger')
                    ->formatStateUsing(fn (string $state): string => $state === StockAdjustment::TYPE_INCREASE ? 'Penambahan' : 'Pengurangan'),
                TextColumn::make('adjustable_type')
                    ->label('Jenis')
                    ->formatStateUsing(fn ($state) => StockAdjustment::ADJUSTABLE_TYPES[$state] ?? $state)
                    ->sortable(),
                TextColumn::make('ingredient.name')
                    ->label('Nama')
                    ->state(fn (StockAdjustment $record) =>
                        $record->isMenuAdjustment()
                            ? $record->menu?->name
                            : $record->ingredient?->name
                    )
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->formatStateUsing(fn ($state) => StockAdjustment::DECREASE_CATEGORIES[$state]
                        ?? StockAdjustment::INCREASE_CATEGORIES[$state]
                        ?? $state)
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => $state === 'cancelled' ? 'danger' : 'success')
                    ->formatStateUsing(fn (?string $state): string => $state === 'cancelled' ? 'Dibatalkan' : 'Aktif')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state, StockAdjustment $record) =>
                        ($record->adjustment_type === StockAdjustment::TYPE_INCREASE ? '+' : '-')
                        . \App\Filament\Resources\StockAdjustmentResource::formatNumber((float) $state)
                        . ' ' . ($record->isMenuAdjustment() ? 'porsi' : ($record->ingredient?->unit ?? ''))
                    )
                    ->color(fn (StockAdjustment $record): string =>
                        $record->adjustment_type === StockAdjustment::TYPE_DECREASE ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('quantity_before')
                    ->label('Sebelum')
                    ->formatStateUsing(fn ($state, StockAdjustment $record) =>
                        $record->isMenuAdjustment()
                            ? '-'
                            : \App\Filament\Resources\StockAdjustmentResource::formatNumber((float) $state)
                                . ' ' . ($record->ingredient?->unit ?? '')
                    )
                    ->sortable(),
                TextColumn::make('quantity_after')
                    ->label('Sesudah')
                    ->formatStateUsing(fn ($state, StockAdjustment $record) =>
                        $record->isMenuAdjustment()
                            ? '-'
                            : \App\Filament\Resources\StockAdjustmentResource::formatNumber((float) $state)
                                . ' ' . ($record->ingredient?->unit ?? '')
                    )
                    ->sortable(),
                TextColumn::make('reportedBy.name')
                    ->label('Dilaporkan Oleh')
                    ->default('-')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('adjustable_type')
                    ->label('Jenis')
                    ->options(StockAdjustment::ADJUSTABLE_TYPES),
                SelectFilter::make('adjustment_type')
                    ->label('Tipe')
                    ->options([
                        StockAdjustment::TYPE_INCREASE => 'Penambahan',
                        StockAdjustment::TYPE_DECREASE => 'Pengurangan',
                    ]),
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(StockAdjustment::DECREASE_CATEGORIES + StockAdjustment::INCREASE_CATEGORIES),
            ])
            ->recordActions([
                \App\Filament\Resources\StockAdjustmentResource\Actions\CancelAdjustmentAction::make(),
                \App\Filament\Resources\StockAdjustmentResource\Actions\DetailAdjustmentAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('adjusted_at', 'desc');
    }
}
