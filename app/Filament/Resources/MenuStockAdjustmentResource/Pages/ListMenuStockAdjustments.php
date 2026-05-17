<?php

namespace App\Filament\Resources\MenuStockAdjustmentResource\Pages;

use App\Filament\Resources\MenuStockAdjustmentResource;
use App\Services\MenuStockReconciliationService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListMenuStockAdjustments extends ListRecords
{
    protected static string $resource = MenuStockAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Penyesuaian Baru')
                ->modal()
                ->modalWidth('2xl')
                ->using(function (array $data): Model {
                    $rawQuantity = (string) $data['quantity'];
                    $parsedQuantity = (float) str_replace(',', '.', str_replace('.', '', $rawQuantity));

                    /** @var MenuStockReconciliationService $service */
                    $service = app(MenuStockReconciliationService::class);

                    return $service->createManualAdjustment(
                        menuStockId: (int) $data['menu_stock_id'],
                        quantity: $parsedQuantity,
                        adjustmentType: (string) $data['adjustment_type'],
                        reason: (string) $data['reason'],
                        reportedBy: isset($data['reported_by']) ? (int) $data['reported_by'] : null,
                        adjustedAt: $data['adjusted_at'] ?? null,
                    );
                }),
        ];
    }
}
