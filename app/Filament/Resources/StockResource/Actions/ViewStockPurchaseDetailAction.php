<?php

namespace App\Filament\Resources\StockResource\Actions;

use App\Enums\MovementType;
use App\Models\StockMovement;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;

class ViewStockPurchaseDetailAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->name('view_purchase')
            ->label('Detail')
            ->icon(Heroicon::OutlinedEye)
            ->modalHeading('Detail Pembelian Batch')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup')
            ->modalAutofocus(false)
            ->visible(fn (StockMovement $record): bool => $record->movement_type === MovementType::Purchase)
            ->infolist(fn (StockMovement $record): array => [
                Section::make('Informasi Batch')
                    ->schema([
                        TextEntry::make('ingredientBatch.batch_code')->label('Kode Batch')->copyable(),
                        TextEntry::make('ingredientBatch.received_at')->label('Waktu Diterima')->dateTime('d M Y, H:i:s'),
                        TextEntry::make('ingredientBatch.expiry_date')
                            ->label('Tanggal Kedaluwarsa')
                            ->default('-')
                            ->formatStateUsing(fn ($state) => $state === '-' ? '-' : Carbon::parse($state)->translatedFormat('d M Y')),
                        TextEntry::make('ingredientBatch.quantity')
                            ->label('Quantity Awal')
                            ->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                        TextEntry::make('ingredientBatch.total_cost')
                            ->label('Harga Total')
                            ->formatStateUsing(fn ($state, $record) => 'Rp' . number_format((float) $state, 0, ',', '.')
                                . ' / ' . number_format((float) ($record->ingredientBatch?->initial_quantity ?? $record->ingredientBatch?->quantity ?? 0), 2)
                                . ' ' . ($record->ingredientBatch?->ingredient?->unit?->value ?? '')),
                        TextEntry::make('ingredientBatch.allow_expired_usage')
                            ->label('Bisa Kedaluwarsa')
                            ->formatStateUsing(fn ($state): string => $state ? 'Ya' : 'Tidak'),
                    ])->columns(3),
                Section::make('Statistik Pemakaian')
                    ->schema([
                        TextEntry::make('quantity_before')->label('Quantity Awal')->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                        TextEntry::make('quantity_change')->label('Perubahan')->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                        TextEntry::make('quantity_after')->label('Sisa')->formatStateUsing(fn ($state) => number_format((float) $state, 2)),
                    ])->columns(3),
            ]);
    }
}
