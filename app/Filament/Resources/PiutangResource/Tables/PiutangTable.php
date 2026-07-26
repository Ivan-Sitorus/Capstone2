<?php

namespace App\Filament\Resources\PiutangResource\Tables;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
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
                ->with('cashier')
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
                    ->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Dibayar')
                    ->getStateUsing(function (Order $record) {
                        return (float) $record->orderPayments->sum('amount');
                    })
                    ->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.')),
                TextColumn::make('remaining_amount')
                    ->label('Sisa')
                    ->getStateUsing(function (Order $record) {
                        return (float) $record->total_amount - (float) $record->orderPayments->sum('amount');
                    })
                    ->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.'))
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => \App\Filament\Resources\OrderResource::getStatusColor($state))
                    ->formatStateUsing(fn (string $state): string => \App\Filament\Resources\OrderResource::getStatusLabel($state)),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Order $record) => OrderResource::getUrl('view', ['record' => $record])),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
