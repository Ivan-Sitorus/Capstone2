<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Filament\Resources\StockResource;
use App\Models\Ingredient;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\StockAdjustmentResource;

class ViewStockHistory extends ListRecords
{
    protected static string $resource = StockResource::class;

    protected static ?string $breadcrumb = 'Riwayat Stok';

    public ?Ingredient $ingredient = null;

    public function mount(): void
    {
        $this->ingredient = Ingredient::findOrFail(request()->route('record'));
        parent::mount();
    }

    public function getTitle(): string
    {
        return 'Riwayat Stok: ' . ($this->ingredient?->name ?? '');
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return StockMovement::with(['order', 'stockAdjustment', 'ingredientBatch'])
            ->where('ingredient_id', $this->ingredient->id);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->getTableQuery())
            ->searchPlaceholder('Cari...')
            ->filters([])
            ->recordAction(null)
            ->columns([
                // 1. Waktu
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),

                // 2. Jenis Pemakaian
                TextColumn::make('movement_type')
                    ->label('Jenis Pemakaian')
                    ->badge()
                    ->color(fn (StockMovement $record): string => match (true) {
                        $record->source_type === 'stock_adjustment_reversal' => 'gray',
                        $record->movement_type === 'sale' => 'primary',
                        $record->movement_type === 'purchase' => 'success',
                        $record->stockAdjustment?->category === StockAdjustment::CAT_EXPIRED => 'danger',
                        $record->movement_type === 'waste' => 'danger',
                        $record->movement_type === 'purchase' => 'success',
                        in_array($record->movement_type, ['adjustment_increase', 'adjustment_decrease']) => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (StockMovement $record): string => match (true) {
                        $record->source_type === 'stock_adjustment_reversal'
                            => 'Pembatalan Penyesuaian',
                        $record->stockAdjustment?->category === StockAdjustment::CAT_EXPIRED
                            => 'Kedaluwarsa',
                        $record->movement_type === 'sale' => 'Penjualan',
                        $record->movement_type === 'purchase' => 'Pembelian',
                        $record->movement_type === 'waste' => 'Penyesuaian',
                        in_array($record->movement_type, ['adjustment_increase', 'adjustment_decrease'])
                            => 'Penyesuaian',
                        default => $record->movement_type,
                    })
                    ->tooltip(fn (StockMovement $record): string => match (true) {
                        $record->movement_type === 'sale'
                            => 'Stok berkurang karena ada pesanan penjualan',
                        $record->movement_type === 'purchase'
                            => 'Stok bertambah karena ada pembelian',
                        $record->source_type === 'stock_adjustment_reversal'
                            => 'Kembalikan stok akibat penyesuaian dibatalkan',
                        $record->stockAdjustment?->category === StockAdjustment::CAT_EXPIRED
                            => 'Stok berkurang karena batch sudah kedaluwarsa',
                        $record->movement_type === 'waste'
                            => 'Stok berkurang karena bahan kedaluwarsa/rusak/tumpah',
                        in_array($record->movement_type, ['adjustment_increase', 'adjustment_decrease'])
                            => 'Stok disesuaikan secara manual',
                        default => '',
                    }),

                // 3. Referensi
                TextColumn::make('reference')
                    ->label('Referensi')
                    ->state(fn (StockMovement $record): string => match (true) {
                        $record->movement_type === 'sale' && $record->order
                            => $record->order->order_code,
                        $record->movement_type === 'purchase' && $record->ingredientBatch
                            => $record->ingredientBatch->batch_code,
                        (bool) $record->stock_adjustment_id
                            => \App\Models\StockAdjustment::find($record->stock_adjustment_id)?->code
                                ?? ('ADJ-' . str_pad($record->stock_adjustment_id, 3, '0', STR_PAD_LEFT)),
                        default => '-',
                    }),

                // 4. Batch
                TextColumn::make('ingredient_batch_id')
                    ->label('Batch')
                    ->formatStateUsing(fn (StockMovement $record): string =>
                        $record->ingredientBatch?->batch_code
                        ?? '#' . ($record->ingredient_batch_id ?? '-')
                    ),

                // 5. Perubahan
                TextColumn::make('quantity_change')
                    ->label('Perubahan')
                    ->formatStateUsing(fn ($state) =>
                        number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.')
                        . ' ' . ($this->ingredient?->unit ?? '')
                    )
                    ->color(fn (StockMovement $record): string => $record->quantity_change < 0 ? 'danger' : 'success')
                    ->sortable(),

                // 6. Sebelum
                TextColumn::make('quantity_before')
                    ->label('Sebelum')
                    ->formatStateUsing(fn ($state) =>
                        number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.')
                        . ' ' . ($this->ingredient?->unit ?? '')
                    )
                    ->sortable(),

                // 7. Sesudah
                TextColumn::make('quantity_after')
                    ->label('Sesudah')
                    ->formatStateUsing(fn ($state) =>
                        number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.')
                        . ' ' . ($this->ingredient?->unit ?? '')
                    )
                    ->sortable(),


            ])
            ->recordActions([
                Action::make('view_adjustment')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->visible(fn (StockMovement $record): bool =>
                        (bool) $record->stock_adjustment_id)
                    ->infolist(function (StockMovement $record): array {
                        $record->loadMissing('stockAdjustment.ingredient', 'stockAdjustment.menu', 'stockAdjustment.reportedBy');
                        return StockAdjustmentResource::getInfolistComponents(prefix: 'stockAdjustment.');
                    })
                    ->modalAutofocus(false)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                Action::make('view_order')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->visible(fn (StockMovement $record): bool =>
                        $record->movement_type === 'sale' && (bool) $record->order_id)
                    ->infolist(function (StockMovement $record): array {
                        $record->loadMissing('order.items.menu', 'order.cashier');
                        return OrderResource::getInfolistComponents(prefix: 'order.');
                    })
                    ->modalAutofocus(false)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private function isCancelled(StockMovement $record): bool
    {
        return match (true) {
            $record->movement_type === 'correction' => false,
            $record->movement_type === 'sale'
                && $record->order?->status === 'dibatalkan' => true,
            default => false,
        };
    }
}
