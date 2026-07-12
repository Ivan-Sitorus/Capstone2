<?php

namespace App\Filament\Resources\StockAdjustmentResource\Actions;

use App\Filament\Resources\StockAdjustmentResource;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class DetailAdjustmentAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->name('detail')
            ->label('Detail')
            ->icon(Heroicon::OutlinedEye)
            ->infolist(StockAdjustmentResource::getInfolistComponents())
            ->modalAutofocus(false)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup');
    }
}
