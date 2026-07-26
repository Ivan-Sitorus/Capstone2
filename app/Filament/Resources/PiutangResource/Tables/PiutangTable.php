<?php

namespace App\Filament\Resources\PiutangResource\Tables;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PiutangTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->where('status', OrderStatus::BelumLunas->value)
                ->where('payment_method', 'bayar_nanti')
            )
            ->columns([
                TextColumn::make('order_code')
                    ->label('Kode Pesanan')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->default('-')
                    ->searchable(),
                TextColumn::make('cashier.name')
                    ->label('Kasir')
                    ->default('-'),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Dibayar')
                    ->getStateUsing(function (Order $record) {
                        return (float) $record->orderPayments->sum('amount');
                    })
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.')),
                TextColumn::make('remaining_amount')
                    ->label('Sisa')
                    ->getStateUsing(function (Order $record) {
                        return (float) $record->total_amount - (float) $record->orderPayments->sum('amount');
                    })
                    ->formatStateUsing(fn ($state) => 'Rp' . number_format($state, 0, ',', '.'))
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
