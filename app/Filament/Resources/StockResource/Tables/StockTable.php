<?php

namespace App\Filament\Resources\StockResource\Tables;

use App\Models\Ingredient;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class StockTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari Nama Bahan')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Bahan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('Unit')
                    ->sortable(),
                TextColumn::make('nearest_expiry')
                    ->label('Kedaluwarsa Terdekat')
                    ->getStateUsing(fn (Ingredient $record) =>
                        $record->batches()
                            ->where('quantity', '>', 0)
                            ->whereNotNull('expiry_date')
                            ->min('expiry_date')
                    )
                    ->date('d M Y')
                    ->color(fn ($state) => $state && \Carbon\Carbon::parse($state)->isPast() ? 'danger' : null)
                    ->sortable(query: fn ($query, string $direction) =>
                        $query->orderByRaw('(SELECT MIN(expiry_date) FROM ingredient_batches WHERE ingredient_id = ingredients.id AND quantity > 0 AND expiry_date IS NOT NULL) '.$direction)
                    ),
                TextColumn::make('low_stock_threshold')
                    ->label('Peringatan Stok Rendah')
                    ->formatStateUsing(fn ($state) => 
                        $state === null || $state === '' 
                            ? '' 
                            : number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.')
                    )
                    ->suffix(fn (Ingredient $record) => ' '.$record->unit)
                    ->sortable(),
                TextColumn::make('total_stock')
                    ->label('Total Stok')
                    ->getStateUsing(fn (Ingredient $record) => $record->getTotalStock() + 0)
                    ->suffix(fn (Ingredient $record) => ' '.$record->unit)
                    ->badge()
                    ->color(fn (Ingredient $record) => $record->getTotalStock() < (float) $record->low_stock_threshold ? 'danger' : 'success')
                    ->sortable(query: function ($query, string $direction): void {
                        $query->orderByRaw('(SELECT COALESCE(SUM(quantity), 0) FROM ingredient_batches WHERE ingredient_batches.ingredient_id = ingredients.id) '.$direction);
                    }),
                TextColumn::make('batch_mode')
                    ->label('Prioritas Batch')
                    ->badge()
                    ->color(fn ($state) => $state === 'fifo' ? 'info' : 'warning')
                    ->formatStateUsing(fn ($state) => strtoupper($state)),
            ])
            ->filters([
                Filter::make('low_stock')
                    ->label('Stok Rendah')
                    ->query(fn ($query) => $query->whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM ingredient_batches WHERE ingredient_batches.ingredient_id = ingredients.id) < low_stock_threshold')),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('batches')
                        ->label('Stok Bahan')
                        ->icon(Heroicon::OutlinedCube)
                        ->url(fn ($record) => \App\Filament\Resources\StockResource::getUrl('batches', ['record' => $record])),
                    Action::make('history')
                        ->label('Riwayat Pemakaian')
                        ->icon(Heroicon::OutlinedClock)
                        ->url(fn ($record) => \App\Filament\Resources\StockResource::getUrl('history', ['record' => $record])),
                    EditAction::make()->modal(),
                    DeleteAction::make()
                        ->before(function (DeleteAction $action, Ingredient $record) {
                        $activeCount = $record->menuIngredients()
                            ->whereHas('menu')
                            ->count();
                            
                            if ($activeCount > 0) {
                                Notification::make()
                                    ->danger()
                                    ->title("Bahan baku '{$record->name}' tidak dapat dihapus")
                                    ->body("Masih digunakan oleh {$activeCount} menu. Gunakan filter Bahan Baku di halaman Menu untuk melihat daftarnya.")
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ])
                ->icon(Heroicon::OutlinedEllipsisVertical),
            ])
            ->defaultSort('nearest_expiry', 'asc');
    }
}
