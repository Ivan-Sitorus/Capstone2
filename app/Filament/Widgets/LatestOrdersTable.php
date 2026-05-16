<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class LatestOrdersTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    public function getTableHeading(): string
    {
        return 'Transaksi Terbaru';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->where('is_paid', true)
                    ->with(['items.menu'])
                    ->latest()
                    ->limit(10)
            )
            ->heading($this->getTableHeading())
            ->paginated(false)
            ->columns([
                TextColumn::make('order_code')
                    ->label('ID Pesanan')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('items_summary')
                    ->label('Items')
                    ->getStateUsing(function (Order $record): string {
                        return $record->items
                            ->map(fn ($item) => "{$item->quantity}x {$item->menu->name}")
                            ->implode(', ');
                    }),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn (int $state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->weight('bold'),
                TextColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'qris' => 'info',
                        'transfer' => 'warning',
                        'bayar_nanti' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'qris' => 'QRIS',
                        'transfer' => 'Transfer',
                        'bayar_nanti' => 'Bayar Nanti',
                        default => ucfirst($state),
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Order::STATUS_PENDING => 'warning',
                        Order::STATUS_DIPROSES => 'info',
                        Order::STATUS_SELESAI => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Order::STATUS_PENDING => 'Pending',
                        Order::STATUS_DIPROSES => 'Diproses',
                        Order::STATUS_SELESAI => 'Selesai',
                        default => ucfirst($state),
                    }),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ]);
    }
}
