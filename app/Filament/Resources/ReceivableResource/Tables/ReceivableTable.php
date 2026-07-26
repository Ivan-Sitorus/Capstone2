<?php

namespace App\Filament\Resources\ReceivableResource\Tables;

use App\Models\Receivable;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReceivableTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_code')
                    ->label('Pesanan')
                    ->searchable()
                    ->url(fn (Receivable $record): ?string => $record->order
                        ? route('filament.admin.resources.orders.view', $record->order)
                        : null)
                    ->openUrlInNewTab(),
                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Dibayar')
                    ->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('remaining_amount')
                    ->label('Sisa')
                    ->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.')),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Receivable::STATUS_PAID => 'success',
                        Receivable::STATUS_CANCELLED => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        Receivable::STATUS_PENDING => 'Pending',
                        Receivable::STATUS_PAID => 'Lunas',
                        Receivable::STATUS_CANCELLED => 'Dibatalkan',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('cancel')
                    ->label('Batalkan')
                    ->color('danger')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->action(fn ($record, array $data) => $record->cancel($data['reason'] ?? null))
                    ->form([Textarea::make('reason')->label('Alasan Pembatalan')->required()])
                    ->visible(fn ($record) => !in_array($record->status, ['paid', 'cancelled']))
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }
}
