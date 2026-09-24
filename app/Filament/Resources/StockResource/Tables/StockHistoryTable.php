<?php

namespace App\Filament\Resources\StockResource\Tables;

use App\Enums\MovementType;
use App\Filament\Resources\StockResource\Actions\ViewStockAdjustmentDetailAction;
use App\Filament\Resources\StockResource\Actions\ViewStockOrderDetailAction;
use App\Filament\Resources\StockResource\Actions\ViewStockPurchaseDetailAction;
use App\Models\Ingredient;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockHistoryTable
{
    public static function configure(Table $table, Ingredient $ingredient): Table
    {
        return $table
            ->query(StockMovement::query()
                ->where('ingredient_id', $ingredient->id)
                ->with(['order', 'stockAdjustment', 'ingredientBatch']))
            ->searchPlaceholder('Cari Referensi / Batch...')
            ->filters([
                Filter::make('created_at')
                    ->label('Rentang Waktu')
                    ->form([
                        DatePicker::make('created_from')->label('Dari'),
                        DatePicker::make('created_until')->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        ),
                    ),
                SelectFilter::make('movement_type')
                    ->label('Jenis Pemakaian')
                    ->options([
                        MovementType::Sale->value => 'Penjualan',
                        MovementType::Purchase->value => 'Pembelian',
                        MovementType::Waste->value => 'Kedaluwarsa/Rusak',
                        MovementType::AdjustmentIncrease->value => 'Penambahan Manual',
                        MovementType::AdjustmentDecrease->value => 'Pengurangan Manual',
                        MovementType::Correction->value => 'Koreksi',
                    ]),
            ])
            ->recordAction(null)
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
                TextColumn::make('movement_type')
                    ->label('Jenis Pemakaian')
                    ->badge()
                    ->color(fn (StockMovement $record): string => match (true) {
                        $record->movement_type === MovementType::Sale => 'primary',
                        $record->movement_type === MovementType::Purchase => 'success',
                        $record->movement_type === MovementType::Waste => 'danger',
                        in_array($record->movement_type, [MovementType::AdjustmentIncrease, MovementType::AdjustmentDecrease]) => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (StockMovement $record): string => match (true) {
                        $record->movement_type === MovementType::Sale => 'Penjualan',
                        $record->movement_type === MovementType::Purchase => 'Pembelian',
                        $record->movement_type === MovementType::Waste => 'Kedaluwarsa/Rusak',
                        in_array($record->movement_type, [MovementType::AdjustmentIncrease, MovementType::AdjustmentDecrease]) => 'Penyesuaian',
                        default => $record->movement_type->value,
                    })
                    ->tooltip(fn (StockMovement $record): string => match (true) {
                        $record->movement_type === MovementType::Sale => 'Stok berkurang karena ada pesanan penjualan',
                        $record->movement_type === MovementType::Purchase => 'Stok bertambah karena ada pembelian',
                        $record->movement_type === MovementType::Waste => 'Stok berkurang karena bahan kedaluwarsa/rusak/tumpah',
                        in_array($record->movement_type, [MovementType::AdjustmentIncrease, MovementType::AdjustmentDecrease]) => 'Stok disesuaikan secara manual',
                        default => '',
                    }),
                TextColumn::make('reference')
                    ->label('Referensi')
                    ->searchable()
                    ->state(fn (StockMovement $record): string => match (true) {
                        $record->movement_type === MovementType::Sale && $record->order => $record->order->order_code,
                        $record->movement_type === MovementType::Purchase && $record->ingredientBatch => $record->ingredientBatch->batch_code,
                        (bool) $record->stock_adjustment_id => StockAdjustment::find($record->stock_adjustment_id)?->code
                            ?? ('ADJ-' . str_pad($record->stock_adjustment_id, 3, '0', STR_PAD_LEFT)),
                        default => '-',
                    }),
                TextColumn::make('ingredient_batch_id')
                    ->label('Batch')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->orWhereHas('ingredientBatch', fn (Builder $q): Builder => $q->where('batch_code', 'like', "%{$search}%"))
                    )
                    ->formatStateUsing(fn (StockMovement $record): string => $record->ingredientBatch?->batch_code ?? '#' . ($record->ingredient_batch_id ?? '-')),
                TextColumn::make('quantity_change')
                    ->label('Perubahan')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.') . ' ' . ($ingredient->unit?->value ?? ''))
                    ->color(fn (StockMovement $record): string => $record->quantity_change < 0 ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('quantity_before')
                    ->label('Sebelum')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.') . ' ' . ($ingredient->unit?->value ?? ''))
                    ->sortable(),
                TextColumn::make('quantity_after')
                    ->label('Sesudah')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.') . ' ' . ($ingredient->unit?->value ?? ''))
                    ->sortable(),
            ])
            ->recordActions([
                ViewStockAdjustmentDetailAction::make(),
                ViewStockOrderDetailAction::make(),
                ViewStockPurchaseDetailAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
