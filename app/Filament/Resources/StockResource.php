<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Forms\StockForm;
use App\Filament\Resources\StockResource\Pages\ListStocks;
use App\Filament\Resources\StockResource\Pages\ManageBatches;
use App\Filament\Resources\StockResource\Pages\ViewStockHistory;
use App\Filament\Resources\StockResource\Tables\StockTable;
use App\Models\Ingredient;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StockResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedCube;

    protected static bool $shouldRegisterNavigation = true;

    protected static string | UnitEnum | null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Bahan Baku';

    protected static ?string $pluralLabel = 'Bahan Baku';

    protected static ?string $label = 'Bahan Baku';

    protected static ?string $slug = 'bahan-baku';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return StockForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStocks::route('/'),
            'batches' => ManageBatches::route('/{record}/batches'),
            'history' => ViewStockHistory::route('/{record}/history'),
            'riwayat-bayar-batch' => \App\Filament\Resources\StockResource\Pages\RiwayatBayarBatch::route('/{record}/riwayat-bayar'),
        ];
    }
}
