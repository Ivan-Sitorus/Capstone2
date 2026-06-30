<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Resources\OrderResource\RelationManagers\ItemsRelationManager;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

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
                    TextEntry::make($p.'customer_phone')->label('No. HP')->default('-'),
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
                        ->formatStateUsing(fn (?string $state): string => match ($state) {
                            'cash' => 'Tunai',
                            'qris' => 'QRIS',
                            'bayar_nanti' => 'Bayar Nanti',
                            default => '-',
                        }),
                    TextEntry::make($p.'total_amount')->label('Total')->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.')),
                    TextEntry::make($p.'status')->label('Status Pesanan')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'pending' => 'warning',
                            'diproses' => 'info',
                            'selesai' => 'success',
                            default => 'gray',
                        })
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'pending' => 'Pending',
                            'diproses' => 'Diproses',
                            'selesai' => 'Selesai',
                            default => $state,
                        }),
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
        return $table
            ->columns([
                TextColumn::make('order_code')
                    ->label('Kode Pesanan')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('customer_name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->default('Guest'),
                TextColumn::make('cashier.name')
                    ->label('Kasir')
                    ->searchable()
                    ->default('-'),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => 'Rp'.number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'qris' => 'QRIS',
                        'bayar_nanti' => 'Bayar Nanti',
                        default => '-',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'diproses' => 'info',
                        'selesai' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                        default => $state,
                    }),
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['items.menu', 'cashier']))
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                    ]),
                SelectFilter::make('payment_method')
                    ->label('Metode Bayar')
                    ->options([
                        'cash' => 'Tunai',
                        'qris' => 'QRIS',
                        'bayar_nanti' => 'Bayar Nanti',
                    ]),
                Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->toggle(),
                Filter::make('this_week')
                    ->label('Minggu Ini')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ]))
                    ->toggle(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->record(fn (Order $record): Order => $record->loadMissing('items.menu'))
                    ->infolist(static::getInfolistComponents())
                    ->modalAutofocus(false)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
        ];
    }


}
