<?php

namespace App\Filament\Resources\StockAdjustmentResource\Tables;

use App\Enums\AdjustableType;
use App\Enums\AdjustmentCategory;
use App\Enums\AdjustmentType;
use App\Models\StockAdjustment;
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
                    ->color(fn (AdjustmentType $state): string => $state === AdjustmentType::Increase ? 'primary' : 'danger')
                    ->formatStateUsing(fn (AdjustmentType $state): string => $state === AdjustmentType::Increase ? 'Penambahan' : 'Pengurangan'),
                TextColumn::make('adjustable_type')
                    ->label('Jenis')
                    ->formatStateUsing(fn (?AdjustableType $state): string => $state === AdjustableType::Ingredient ? 'Bahan Baku' : 'Menu')
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
                    ->formatStateUsing(fn (?AdjustmentCategory $state): string => $state?->label() ?? '-')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state, StockAdjustment $record) =>
                        ($record->adjustment_type === AdjustmentType::Increase ? '+' : '-')
                        . \App\Filament\Resources\StockAdjustmentResource::formatNumber((float) $state)
                        . ' ' . ($record->isMenuAdjustment() ? 'porsi' : ($record->ingredient?->unit ?? ''))
                    )
                    ->color(fn (StockAdjustment $record): string =>
                        $record->adjustment_type === AdjustmentType::Decrease ? 'danger' : 'success')
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
                SelectFilter::make('adjustable_type')
                    ->label('Jenis')
                    ->options([
                        AdjustableType::Ingredient->value => 'Bahan Baku',
                        AdjustableType::Menu->value => 'Menu',
                    ]),
                SelectFilter::make('adjustment_type')
                    ->label('Tipe')
                    ->options([
                        AdjustmentType::Increase->value => 'Penambahan',
                        AdjustmentType::Decrease->value => 'Pengurangan',
                    ]),
                SelectFilter::make('category')
                    ->label('Kategori')
                    ->options(collect(AdjustmentCategory::cases())
                        ->mapWithKeys(fn (AdjustmentCategory $c) => [$c->value => $c->label()])
                        ->toArray()),
            ])
            ->recordActions([
                \App\Filament\Resources\StockAdjustmentResource\Actions\DetailAdjustmentAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('adjusted_at', 'desc');
    }
}
