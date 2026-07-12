<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyIngredientUsageResource\Forms\DailyIngredientUsageForm;
use App\Filament\Resources\DailyIngredientUsageResource\Pages\ListDailyIngredientUsages;
use App\Filament\Resources\DailyIngredientUsageResource\Tables\DailyIngredientUsageTable;
use App\Models\DailyIngredientUsage;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DailyIngredientUsageResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = DailyIngredientUsage::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static string | UnitEnum | null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Pemakaian Bahan Harian';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return DailyIngredientUsageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailyIngredientUsageTable::configure($table);
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
            'index' => ListDailyIngredientUsages::route('/'),
        ];
    }
}
