<?php

namespace App\Filament\Resources\StockResource\Pages;

use App\Enums\MovementType;
use App\Filament\Resources\StockResource;
use App\Models\Ingredient;
use App\Models\StockMovement;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\StockAdjustmentResource;
use Filament\Support\Icons\Heroicon;

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
                        $record->movement_type === MovementType::Sale => 'primary',
                        $record->movement_type === MovementType::Purchase => 'success',
                        $record->movement_type === MovementType::Waste => 'danger',
                        in_array($record->movement_type, [MovementType::AdjustmentIncrease, MovementType::AdjustmentDecrease]) => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (StockMovement $record): string => match (true) {
                        $record->movement_type === MovementType::Sale => 'Penjualan',
                        $record->movement_type === MovementType::Purchase => 'Pembelian',
                        $record->movement_type === MovementType::Waste => 'Penyesuaian',
                        in_array($record->movement_type, [MovementType::AdjustmentIncrease, MovementType::AdjustmentDecrease])
                            => 'Penyesuaian',
                        default => $record->movement_type->value,
                    })
                    ->tooltip(fn (StockMovement $record): string => match (true) {
                        $record->movement_type === MovementType::Sale
                            => 'Stok berkurang karena ada pesanan penjualan',
                        $record->movement_type === MovementType::Purchase
                            => 'Stok bertambah karena ada pembelian',
                        $record->movement_type === MovementType::Waste
                            => 'Stok berkurang karena bahan kedaluwarsa/rusak/tumpah',
                        in_array($record->movement_type, [MovementType::AdjustmentIncrease, MovementType::AdjustmentDecrease])
                            => 'Stok disesuaikan secara manual',
                        default => '',
                    }),

                // 3. Referensi
                TextColumn::make('reference')
                    ->label('Referensi')
                    ->state(fn (StockMovement $record): string => match (true) {
                        $record->movement_type === MovementType::Sale && $record->order
                            => $record->order->order_code,
                        $record->movement_type === MovementType::Purchase && $record->ingredientBatch
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

                // 5. Change
                TextColumn::make('quantity_change')
                    ->label('Perubahan')
                    ->formatStateUsing(fn ($state) =>
                        number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.')
                        . ' ' . ($this->ingredient?->unit?->value ?? '')
                    )
                    ->color(fn (StockMovement $record): string => $record->quantity_change < 0 ? 'danger' : 'success')
                    ->sortable(),

                // 6. Before
                TextColumn::make('quantity_before')
                    ->label('Sebelum')
                    ->formatStateUsing(fn ($state) =>
                        number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.')
                        . ' ' . ($this->ingredient?->unit?->value ?? '')
                    )
                    ->sortable(),

                // 7. Sesudah
                TextColumn::make('quantity_after')
                    ->label('Sesudah')
                    ->formatStateUsing(fn ($state) =>
                        number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.')
                        . ' ' . ($this->ingredient?->unit?->value ?? '')
                    )
                    ->sortable(),


            ])
            ->recordActions([
                Action::make('view_adjustment')
                    ->label('Detail')
                    ->icon(Heroicon::OutlinedEye)
                    ->visible(fn (StockMovement $record): bool =>
                        (bool) $record->stock_adjustment_id)
                    ->infolist(function (StockMovement $record): array {
                        $record->loadMissing('stockAdjustment.ingredient', 'stockAdjustment.reportedBy');
                        return StockAdjustmentResource::getInfolistComponents(prefix: 'stockAdjustment.');
                    })
                    ->modalAutofocus(false)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                Action::make('view_order')
                    ->label('Detail')
                    ->icon(Heroicon::OutlinedEye)
                    ->visible(fn (StockMovement $record): bool =>
                        $record->movement_type === MovementType::Sale && (bool) $record->order_id)
                    ->infolist(function (StockMovement $record): array {
                        $record->loadMissing('order.items.menu', 'order.cashier');
                        return OrderResource::getInfolistComponents(prefix: 'order.');
                    })
                    ->modalAutofocus(false)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                Action::make('view_purchase')
                    ->label('Detail')
                    ->icon(Heroicon::OutlinedEye)
                    ->modalHeading('Detail Pembelian Batch')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalAutofocus(false)
                    ->visible(fn ($record) => $record->movement_type === MovementType::Purchase)
                    ->infolist(fn ($record) => [
                        \Filament\Infolists\Components\Section::make('Informasi Batch')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('ingredientBatch.batch_code')->label('Kode Batch')->copyable(),
                                \Filament\Infolists\Components\TextEntry::make('ingredientBatch.received_at')->label('Waktu Diterima')->dateTime('d M Y, H:i:s'),
                                \Filament\Infolists\Components\TextEntry::make('ingredientBatch.expiry_date')->label('Tanggal Kedaluwarsa')->default('-')->formatStateUsing(fn ($state) => $state === '-' ? '-' : \Illuminate\Support\Carbon::parse($state)->translatedFormat('d M Y')),
                                \Filament\Infolists\Components\TextEntry::make('ingredientBatch.quantity')->label('Quantity Awal')->formatStateUsing(fn ($state) => number_format((float)$state, 2)),
                                \Filament\Infolists\Components\TextEntry::make('ingredientBatch.total_cost')->label('Harga Total')
                                    ->formatStateUsing(fn ($state, $record) => 'Rp'.number_format((float) $state, 0, ',', '.')
                                        .' / '.number_format((float) ($record->ingredientBatch?->initial_quantity ?? $record->ingredientBatch?->quantity ?? 0), 2)
                                        .' '.($record->ingredientBatch?->ingredient?->unit?->value ?? '')),
                                \Filament\Infolists\Components\TextEntry::make('ingredientBatch.allow_expired_usage')->label('Bisa Kedaluwarsa')->boolean(),
                            ])->columns(3),
                        \Filament\Infolists\Components\Section::make('Statistik Pemakaian')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('quantity_before')->label('Quantity Awal')->formatStateUsing(fn ($state) => number_format((float)$state, 2)),
                                \Filament\Infolists\Components\TextEntry::make('quantity_change')->label('Perubahan')->formatStateUsing(fn ($state) => number_format((float)$state, 2)),
                                \Filament\Infolists\Components\TextEntry::make('quantity_after')->label('Sisa')->formatStateUsing(fn ($state) => number_format((float)$state, 2)),
                            ])->columns(3),
                    ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
