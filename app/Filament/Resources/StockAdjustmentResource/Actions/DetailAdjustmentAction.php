<?php

namespace App\Filament\Resources\StockAdjustmentResource\Actions;

use App\Filament\Resources\StockAdjustmentResource;
use Filament\Actions\Action;

class DetailAdjustmentAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->name('detail')
            ->label('Detail')
            ->icon('heroicon-o-eye')
            ->infolist(StockAdjustmentResource::getInfolistComponents())
            ->modalAutofocus(false)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup');
    }
}
