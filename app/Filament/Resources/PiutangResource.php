<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PiutangResource\Pages\ListPiutangs;
use App\Filament\Resources\PiutangResource\Tables\PiutangTable;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PiutangResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string | UnitEnum | null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Piutang';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return PiutangTable::configure($table);
    }

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function getPages(): array
    {
        return [
            'index' => ListPiutangs::route('/'),
        ];
    }
}
