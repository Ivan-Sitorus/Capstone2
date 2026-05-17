<?php

namespace App\Filament\Resources\MenuStockResource\Pages;

use App\Filament\Resources\MenuStockResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMenuStock extends EditRecord
{
    protected static string $resource = MenuStockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
