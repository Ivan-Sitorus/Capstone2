<?php

namespace App\Filament\Resources\StockResource\Actions;

use App\Enums\MovementType;
use App\Filament\Resources\OrderResource;
use App\Models\StockMovement;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class ViewStockOrderDetailAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->name('view_order')
            ->label('Detail')
            ->icon(Heroicon::OutlinedEye)
            ->visible(fn (StockMovement $record): bool => $record->movement_type === MovementType::Sale && (bool) $record->order_id)
            ->infolist(function (StockMovement $record): array {
                $record->loadMissing('order.items.menu', 'order.cashier');

                return OrderResource::getInfolistComponents(prefix: 'order.');
            })
            ->modalAutofocus(false)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Tutup');
    }
}
