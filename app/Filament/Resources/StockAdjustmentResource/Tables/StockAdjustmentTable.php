<?php

namespace App\Filament\Resources\StockAdjustmentResource\Tables;

use App\Enums\AdjustmentType;
use App\Filament\Resources\StockAdjustmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockAdjustmentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['ingredient', 'reportedBy']))
            ->columns([
                TextColumn::make('adjusted_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
                TextColumn::make('code')
                    ->label('Kode')
                    ->copyable()
                    ->sortable(),
                TextColumn::make('adjustment_type')
                    ->label('Tipe Penyesuaian')
                    ->badge()
                    ->color(fn (AdjustmentType $state): string => $state === AdjustmentType::Increase ? 'primary' : 'danger')
                    ->formatStateUsing(fn (AdjustmentType $state): string => $state === AdjustmentType::Increase ? 'Penambahan' : 'Pengurangan'),
                TextColumn::make('ingredient.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state, $record) =>
                        ($record->adjustment_type === AdjustmentType::Increase ? '+' : '-')
                        . StockAdjustmentResource::formatNumber((float) $state)
                        . ' ' . ($record->ingredient?->unit?->value ?? '')
                    )
                    ->color(fn ($record): string =>
                        $record->adjustment_type === AdjustmentType::Decrease ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('quantity_before')
                    ->label('Sebelum')
                    ->formatStateUsing(fn ($state, $record) =>
                        StockAdjustmentResource::formatNumber((float) $state)
                        . ' ' . ($record->ingredient?->unit?->value ?? '')
                    )
                    ->sortable(),
                TextColumn::make('quantity_after')
                    ->label('Sesudah')
                    ->formatStateUsing(fn ($state, $record) =>
                        StockAdjustmentResource::formatNumber((float) $state)
                        . ' ' . ($record->ingredient?->unit?->value ?? '')
                    )
                    ->sortable(),
                TextColumn::make('reportedBy.name')
                    ->label('Dilaporkan Oleh')
                    ->default('-')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('adjusted_at')
                    ->label('Rentang Waktu')
                    ->form([
                        DatePicker::make('adjusted_from')
                            ->label('Dari'),
                        DatePicker::make('adjusted_until')
                            ->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['adjusted_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('adjusted_at', '>=', $date),
                        )
                        ->when(
                            $data['adjusted_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('adjusted_at', '<=', $date),
                        ),
                    ),
                SelectFilter::make('adjustment_type')
                    ->label('Tipe')
                    ->options([
                        AdjustmentType::Increase->value => 'Penambahan',
                        AdjustmentType::Decrease->value => 'Pengurangan',
                    ]),
            ])
            ->recordAction('detail')
            ->recordActions([
                ActionGroup::make([
                    \App\Filament\Resources\StockAdjustmentResource\Actions\DetailAdjustmentAction::make(),
                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Penyesuaian Stok')
                        ->modalDescription('Stok bahan baku akan dikembalikan ke jumlah semula seperti sebelum penyesuaian ini dibuat. Apakah Anda yakin?')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal'),
                ])
                ->icon(\Filament\Support\Icons\Heroicon::OutlinedEllipsisVertical),
            ])
            ->toolbarActions([])
            ->defaultSort('adjusted_at', 'desc');
    }
}
