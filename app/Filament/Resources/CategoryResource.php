<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Forms\CategoryForm;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Filament\Resources\CategoryResource\Tables\CategoryTable;
use App\Models\Category;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedTag;

    protected static string | UnitEnum | null $navigationGroup = 'Menu';

    protected static ?string $navigationLabel = 'Kategori Menu';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $slug = 'kategori';

    protected static ?string $breadcrumb = 'Kategori Menu';

    protected static ?string $pluralLabel = 'Kategori Menu';

    protected static ?string $label = 'Kategori Menu';

    public static function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoryTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
        ];
    }
}
