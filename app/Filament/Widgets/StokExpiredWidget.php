<?php

namespace App\Filament\Widgets;

use App\Models\IngredientBatch;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StokExpiredWidget extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 1;

    protected function getStats(): array
    {
        $ingredientCount = IngredientBatch::query()
            ->where('quantity', '>', 0)
            ->whereDate('expiry_date', '<', now()->toDateString())
            ->distinct('ingredient_id')
            ->count('ingredient_id');

        return [
            Stat::make('Stok Kedaluwarsa', $ingredientCount)
                ->description('Bahan baku dengan batch sudah kedaluwarsa')
                ->color($ingredientCount > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-calendar-days'),
        ];
    }
}
