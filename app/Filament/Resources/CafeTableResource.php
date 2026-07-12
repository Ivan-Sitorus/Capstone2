<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CafeTableResource\Forms\CafeTableForm;
use App\Filament\Resources\CafeTableResource\Pages\ListCafeTables;
use App\Filament\Resources\CafeTableResource\Tables\CafeTableTable;
use App\Models\CafeTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CafeTableResource extends Resource
{
    protected static ?string $model = CafeTable::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static string | UnitEnum | null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'QR Code Meja';

    protected static ?string $pluralLabel = 'QR Code Meja';

    protected static ?string $label = 'QR Code Meja';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return CafeTableForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CafeTableTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCafeTables::route('/'),
        ];
    }
}
