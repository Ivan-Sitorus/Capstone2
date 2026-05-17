<?php

namespace App\Filament\Pages;

use App\Filament\Resources\MenuStockResource\Pages\ListMenuStocks;
use App\Filament\Resources\StockResource\Pages\ListStocks;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class StokPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Stok';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.stok';

    protected static ?string $slug = 'stok';

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('stok')
                    ->contained(false)
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('ingredients')
                            ->label('Bahan Baku')
                            ->icon('heroicon-o-cube')
                            ->schema([
                                Livewire::make(ListStocks::class),
                            ]),
                        Tab::make('menu_stocks')
                            ->label('Menu')
                            ->icon('heroicon-o-archive-box')
                            ->schema([
                                Livewire::make(ListMenuStocks::class),
                            ]),
                    ]),
            ]);
    }
}
