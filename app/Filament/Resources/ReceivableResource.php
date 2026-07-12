<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReceivableResource\Forms\ReceivableForm;
use App\Filament\Resources\ReceivableResource\Pages\CreateReceivable;
use App\Filament\Resources\ReceivableResource\Pages\EditReceivable;
use App\Filament\Resources\ReceivableResource\Pages\ListReceivables;
use App\Filament\Resources\ReceivableResource\Pages\ViewReceivable;
use App\Filament\Resources\ReceivableResource\Tables\ReceivableTable;
use App\Models\Receivable;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ReceivableResource extends Resource
{
    protected static ?string $model = Receivable::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Piutang';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ReceivableForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Piutang')
                ->schema([
                    TextEntry::make('customer_name')
                        ->label('Nama Pelanggan'),
                    TextEntry::make('invoice_date')
                        ->label('Tanggal Invoice')
                        ->date('d M Y'),
                    TextEntry::make('amount')
                        ->label('Jumlah Total')
                        ->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.')),
                    TextEntry::make('paid_amount')
                        ->label('Jumlah Dibayar')
                        ->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.')),
                    TextEntry::make('remaining_amount')
                        ->label('Sisa')
                        ->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.'))
                        ->color(fn (Receivable $record): string => $record->remaining_amount > 0 ? 'danger' : 'success'),
                    TextEntry::make('due_date')
                        ->label('Jatuh Tempo')
                        ->date('d M Y')
                        ->color(fn (Receivable $record): string => $record->isOverdue() ? 'danger' : 'gray'),
                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'paid' => 'success',
                            'partial' => 'warning',
                            'overdue' => 'danger',
                            default => 'gray',
                        }),
                    TextEntry::make('notes')
                        ->label('Catatan')
                        ->visible(fn ($record) => $record->notes !== null && $record->notes !== ''),
                ])->columns(2),

            Section::make('Informasi Pesanan')
                ->visible(fn (Receivable $record): bool => $record->order !== null)
                ->schema([
                    TextEntry::make('order.order_code')
                        ->label('Kode Pesanan')
                        ->url(fn (Receivable $record): ?string => $record->order
                            ? route('filament.admin.resources.orders.view', $record->order)
                            : null)
                        ->openUrlInNewTab(),
                    TextEntry::make('order.status')
                        ->label('Status Pesanan')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'selesai' => 'success',
                            'diproses' => 'warning',
                            default => 'gray',
                        }),
                    TextEntry::make('order.total_amount')
                        ->label('Total Pesanan')
                        ->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.')),
                ])->columns(3),

            Section::make('Item Pesanan')
                ->visible(fn (Receivable $record): bool => $record->order !== null && $record->order->items()->exists())
                ->schema([
                    TextEntry::make('order.items')
                        ->label('')
                        ->getStateUsing(fn ($record) => $record->order ? $record->order->items->map(fn ($item) => [
                            'menu' => $item->menu?->name ?? 'Unknown',
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'subtotal' => $item->subtotal,
                        ])->toArray() : []),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return ReceivableTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReceivables::route('/'),
            'create' => CreateReceivable::route('/create'),
            'view' => ViewReceivable::route('/{record}'),
            'edit' => EditReceivable::route('/{record}/edit'),
        ];
    }
}
