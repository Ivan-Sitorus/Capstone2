<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Filament\Resources\MenuResource\Pages\ListMenus;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class MenuCategoryPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Menu & Kategori';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Menu & Kategori';

    protected string $view = 'filament.pages.menu-category';

    protected static ?string $slug = 'menu-kategori';

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('menu-category')
                    ->contained(false)
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('menus')
                            ->label('Menu')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Livewire::make(ListMenus::class),
                            ]),
                        Tab::make('categories')
                            ->label('Kategori')
                            ->icon('heroicon-o-tag')
                            ->schema([
                                Livewire::make(ListCategories::class),
                            ]),
                    ]),
            ]);
    }
}
