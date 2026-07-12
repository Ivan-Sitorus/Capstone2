<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffSessionResource\Pages\ListStaffSessions;
use App\Filament\Resources\StaffSessionResource\Pages\ViewStaffSession;
use App\Filament\Resources\StaffSessionResource\Tables\StaffSessionTable;
use App\Models\StaffSession;
use Filament\Resources\Resource;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StaffSessionResource extends Resource
{
    protected static ?string $model = StaffSession::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedClock;

    protected static string | UnitEnum | null $navigationGroup = 'Staff';

    protected static ?string $navigationLabel = 'Riwayat Login Staff';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return StaffSessionTable::configure($table);
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
            'index' => ListStaffSessions::route('/'),
            'view'  => ViewStaffSession::route('/{record}'),
        ];
    }
}
