<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Forms\MenuForm;
use App\Filament\Resources\MenuResource\Pages\ListMenus;
use App\Filament\Resources\MenuResource\RelationManagers\IngredientsRelationManager;
use App\Filament\Resources\MenuResource\Tables\MenuTable;
use App\Models\Menu;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string | UnitEnum | null $navigationGroup = "Menu";

    protected static ?string $navigationLabel = "Menu";

    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $slug = "menu";

    protected static ?string $breadcrumb = "Menu";

    protected static ?string $pluralLabel = "Menu";

    public static function form(Schema $schema): Schema
    {
        return MenuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MenuTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            IngredientsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            "index" => ListMenus::route("/"),
        ];
    }
}
