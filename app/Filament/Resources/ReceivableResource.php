<?php

namespace App\Filament\Resources;

use App\Enums\UserRole;
use App\Filament\Resources\ReceivableResource\Pages\ListReceivables;
use App\Filament\Resources\ReceivableResource\Pages\RiwayatBayar;
use App\Filament\Resources\ReceivableResource\Tables\ReceivableTable;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ReceivableResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string | UnitEnum | null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Piutang';

    protected static ?string $slug = 'piutang';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return ReceivableTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return \Filament\Facades\Filament::auth()->user()?->role === UserRole::Admin;
    }

    public static function canEdit($record): bool
    {
        return \Filament\Facades\Filament::auth()->user()?->role === UserRole::Admin;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReceivables::route('/'),
            'riwayat-bayar' => RiwayatBayar::route('/{record}/pembayaran'),
        ];
    }
}
