<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasOrderStatusBadge;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Resources\OrderResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\OrderResource\Tables\OrderTable;
use App\Models\Order;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class OrderResource extends Resource
{
    use HasOrderStatusBadge;

    protected static ?string $model = Order::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static string | UnitEnum | null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pesanan';

    protected static ?int $navigationSort = 1;

    public static function getInfolistComponents(string $prefix = ''): array
    {
        $p = $prefix; // shorthand

        return [
            Section::make('Informasi Pesanan')
                ->schema([
                    TextEntry::make($p.'order_code')->label('Kode Pesanan')->copyable(),
                    TextEntry::make($p.'created_at')->label('Waktu')->dateTime('d M Y, H:i:s'),
                    TextEntry::make($p.'processed_at')->label('Diproses')->dateTime('d M Y, H:i:s'),
                    TextEntry::make($p.'completed_at')->label('Selesai')->dateTime('d M Y, H:i:s'),
                    TextEntry::make($p.'cancelled_at')->label('Dibatalkan')->dateTime('d M Y, H:i:s'),
                    TextEntry::make($p.'cashier.name')->label('Kasir')->default('-'),
                ])->columns(3),

            Section::make('Pelanggan')
                ->schema([
                    TextEntry::make($p.'customer_name')->label('Nama')->default('Guest'),
                    TextEntry::make($p.'phone')->label('No. HP')->default('-'),
                    TextEntry::make($p.'order_type')->label('Jenis')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'qr' => 'info',
                            'cashier' => 'gray',
                            default => 'gray',
                        })
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'qr' => 'QR Pelanggan',
                            'cashier' => 'Input Kasir',
                            default => $state,
                        }),
                ])->columns(3),

            Section::make('Pembayaran')
                ->schema([
                    TextEntry::make($p.'payment_method')->label('Metode')
                        ->badge()
                        ->formatStateUsing(fn (?string $state): string => self::getPaymentLabel($state)),
                    TextEntry::make($p.'payment_status')->label('Status Bayar')
                        ->badge()
                        ->color(fn (?string $state): string => self::getPaymentStatusColor($state))
                        ->formatStateUsing(fn (?string $state): string => self::getPaymentStatusLabel($state)),
                    TextEntry::make($p.'total_amount')->label('Total')->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.')),
                    TextEntry::make($p.'status')->label('Status Pesanan')
                        ->badge()
                        ->color(fn (string $state): string => self::getStatusColor($state))
                        ->formatStateUsing(fn (string $state): string => self::getStatusLabel($state)),
                ])->columns(3),

            Section::make('Item Pesanan')
                ->schema([
                    RepeatableEntry::make($p.'items')
                        ->hiddenLabel()
                        ->table([
                            TableColumn::make('Menu'),
                            TableColumn::make('Harga')->width(120),
                            TableColumn::make('Jumlah')->width(80),
                            TableColumn::make('Subtotal')->width(120),
                        ])
                        ->schema([
                            TextEntry::make('menu.name'),
                            TextEntry::make('unit_price')
                                ->formatStateUsing(fn ($state) => 'Rp' . number_format((float) $state, 0, ',', '.')),
                            TextEntry::make('quantity'),
                            TextEntry::make('subtotal')
                                ->formatStateUsing(fn ($state) => 'Rp' . number_format((float) $state, 0, ',', '.')),
                        ]),
                ]),
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components(static::getInfolistComponents());
    }

    public static function table(Table $table): Table
    {
        return OrderTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }

}
