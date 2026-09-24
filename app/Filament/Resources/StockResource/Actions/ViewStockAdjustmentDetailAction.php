<?php

namespace App\Filament\Resources\StockResource\Actions;

use App\Filament\Resources\StockAdjustmentResource;
use App\Models\StockMovement;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class ViewStockAdjustmentDetailAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->name('view_adjustment')
            ->label('Detail')
            ->icon(Heroicon::OutlinedEye)
            ->visible(fn (StockMovement $record): bool => (bool) $record->stock_adjustment_id)
            ->infolist(function (StockMovement $record): array {
                $record->loadMissing('stockAdjustment.ingredient', 'stockAdjustment.reportedBy');

                return StockAdjustmentResource::getInfolistComponents(prefix: 'stockAdjustment.');
            })
            ->modalAutofocus(false)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup');
    }
}
