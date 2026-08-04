<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashierHistoryResource\Pages\ListCashierHistories;
use App\Filament\Resources\CashierHistoryResource\Pages\ViewCashierHistory;
use App\Filament\Resources\CashierHistoryResource\Tables\CashierHistoryTable;
use App\Models\CashierHistory;
use Filament\Resources\Resource;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CashierHistoryResource extends Resource
{
    protected static ?string $model = CashierHistory::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedClock;

    protected static string | UnitEnum | null $navigationGroup = 'Staff';

    protected static ?string $navigationLabel = 'Riwayat Kasir';

    protected static ?string $slug = 'riwayat-kasir';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return CashierHistoryTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashierHistories::route('/'),
            'view'  => ViewCashierHistory::route('/{record}'),
        ];
    }
}
