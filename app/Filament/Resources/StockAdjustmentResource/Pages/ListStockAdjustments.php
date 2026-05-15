<?php

namespace App\Filament\Resources\StockAdjustmentResource\Pages;

use App\Filament\Resources\StockAdjustmentResource;
use App\Services\StockReconciliationService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;

class ListStockAdjustments extends ListRecords
{
    protected static string $resource = StockAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modal()
                ->modalWidth('2xl')
                ->using(function (array $data): Model {
                    $rawQuantity = (string) $data['quantity'];
                    $parsedQuantity = (float) str_replace(',', '.', str_replace('.', '', $rawQuantity));

                    /** @var StockReconciliationService $service */
                    $service = app(StockReconciliationService::class);

                    return $service->createManualAdjustment(
                        ingredientId: (int) $data['ingredient_id'],
                        quantity: $parsedQuantity,
                        adjustmentType: (string) $data['adjustment_type'],
                        reason: (string) $data['reason'],
                        reportedBy: $data['reported_by'] ?? null,
                        adjustedAt: $data['adjusted_at'] ?? null,
                    );
                }),
        ];
    }
}
