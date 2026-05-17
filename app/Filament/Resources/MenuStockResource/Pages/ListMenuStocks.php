<?php

namespace App\Filament\Resources\MenuStockResource\Pages;

use App\Filament\Resources\MenuStockResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMenuStocks extends ListRecords
{
    protected static string $resource = MenuStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modal()
                ->modalWidth('2xl'),
        ];
    }
}
