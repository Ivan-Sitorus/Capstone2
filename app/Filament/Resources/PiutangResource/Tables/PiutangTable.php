<?php

namespace App\Filament\Resources\PiutangResource\Tables;

use App\Enums\OrderStatus;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Infolists\Infolist;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PiutangTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->where(function (Builder $q) {
                    $q->where('payment_method', 'piutang')
                      ->orWhere('status', OrderStatus::BelumLunas->value);
                })
                ->with('cashier', 'orderPayments')
            )
            ->searchPlaceholder('Cari Kode Pesanan')
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
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'belum_lunas' => 'Belum Lunas',
                        'selesai' => 'Lunas',
                    ]),
                Filter::make('created_at')
                    ->label('Rentang Waktu')
                    ->form([
                        DatePicker::make('created_from')->label('Dari'),
                        DatePicker::make('created_until')->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['created_from'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                        ->when($data['created_until'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date))
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                ViewAction::make()
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->infolist(fn (Infolist $infolist): Infolist => $infolist
                        ->schema(\App\Filament\Resources\OrderResource::getInfolistComponents())
                    ),
                    Action::make('riwayat_bayar')
                        ->label('Riwayat Bayar')
                        ->icon('heroicon-o-banknotes')
                        ->url(fn (Order $record) => OrderResource::getUrl('view', ['record' => $record])),
                ])
                ->icon(Heroicon::OutlinedEllipsisVertical),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
