<?php

namespace App\Filament\Pages;

use App\Filament\Resources\MenuStockAdjustmentResource\Pages\ListMenuStockAdjustments;
use App\Filament\Resources\StockAdjustmentResource\Pages\ListStockAdjustments;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class AdjustmentsPage extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string | \UnitEnum | null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Penyesuaian Stok';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.adjustments';

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('adjustments')
                    ->contained(false)
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('ingredients')
                            ->label('Bahan Baku')
                            ->icon('heroicon-o-cube')
                            ->schema([
                                Livewire::make(ListStockAdjustments::class),
                            ]),
                        Tab::make('menu_stocks')
                            ->label('Menu')
                            ->icon('heroicon-o-archive-box')
                            ->schema([
                                Livewire::make(ListMenuStockAdjustments::class),
                            ]),
                    ]),
            ]);
    }
}
