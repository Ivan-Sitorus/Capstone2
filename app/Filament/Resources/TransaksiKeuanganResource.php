<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiKeuanganResource\Pages;
use App\Models\TransaksiKeuangan;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TransaksiKeuanganResource extends Resource
{
    protected static ?string $model = TransaksiKeuangan::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Transaksi Keuangan';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Transaksi')
                ->schema([
                    Select::make('transaction_type')
                        ->label('Jenis Transaksi')
                        ->required()
                        ->live()
                        ->options([
                            'pemasukan' => 'Pemasukan',
                            'pengeluaran' => 'Pengeluaran',
                        ]),
                    TextInput::make('source')
                        ->label('Sumber')
                        ->required()
                        ->maxLength(255)
                        ->hidden(fn (Get $get): bool => $get('transaction_type') !== 'pemasukan'),
                    TextInput::make('vendor')
                        ->label('Vendor')
                        ->required()
                        ->maxLength(255)
                        ->hidden(fn (Get $get): bool => $get('transaction_type') !== 'pengeluaran'),
                    Select::make('category')
                        ->label('Kategori')
                        ->required()
                        ->options(fn (Get $get): array => match ($get('transaction_type')) {
                            'pemasukan' => [
                                'penjualan' => 'Penjualan',
                                'modal' => 'Modal',
                                'investasi' => 'Investasi',
                                'lainnya' => 'Lainnya',
                            ],
                            'pengeluaran' => [
                                'bahan_baku' => 'Bahan Baku',
                                'operasional' => 'Operasional',
                                'gaji' => 'Gaji',
                                'utilities' => 'Utilitas',
                                'lainnya' => 'Lainnya',
                            ],
                            default => [],
                        }),
                    TextInput::make('amount')
                        ->label('Jumlah')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->prefix('Rp'),
                    DatePicker::make('date')
                        ->label('Tanggal')
                        ->required()
                        ->native(false),
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->nullable()
                        ->rows(3),
                    Select::make('payment_method')
                        ->label('Metode Pembayaran')
                        ->options([
                            'cash' => 'Tunai',
                            'transfer' => 'Transfer Bank',
                            'qris' => 'QRIS',
                        ])
                        ->hidden(fn (Get $get): bool => $get('transaction_type') !== 'pengeluaran'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pemasukan' => 'success',
                        'pengeluaran' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pemasukan' => 'Pemasukan',
                        'pengeluaran' => 'Pengeluaran',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('source')
                    ->label('Sumber')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('vendor')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'penjualan' => 'success',
                        'modal' => 'info',
                        'investasi' => 'warning',
                        'bahan_baku' => 'danger',
                        'operasional' => 'gray',
                        'gaji' => 'warning',
                        'utilities' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'penjualan' => 'Penjualan',
                        'modal' => 'Modal',
                        'investasi' => 'Investasi',
                        'bahan_baku' => 'Bahan Baku',
                        'operasional' => 'Operasional',
                        'gaji' => 'Gaji',
                        'utilities' => 'Utilitas',
                        'lainnya' => 'Lainnya',
                        default => $state,
                    }),
                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->prefix('Rp')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Metode Bayar')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cash' => 'Tunai',
                        'transfer' => 'Transfer',
                        'qris' => 'QRIS',
                        default => '-',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('transaction_type')
                    ->label('Jenis Transaksi')
                    ->options([
                        '' => 'Semua',
                        'pemasukan' => 'Pemasukan',
                        'pengeluaran' => 'Pengeluaran',
                    ]),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransaksiKeuangans::route('/'),
            'create' => Pages\CreateTransaksiKeuangan::route('/create'),
            'edit' => Pages\EditTransaksiKeuangan::route('/{record}/edit'),
        ];
    }
}
