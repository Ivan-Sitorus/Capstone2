<?php

namespace App\Filament\Widgets;

use App\Models\IngredientBatch;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StokMenipisWidget extends StatsOverviewWidget
{
    protected int | string | array $columnSpan = 1;

    protected function getStats(): array
    {
        $ingredientCount = IngredientBatch::query()
            ->whereNotNull('initial_quantity')
            ->where('quantity', '>', 0)
            ->whereColumn('quantity', '<', \Illuminate\Support\Facades\DB::raw('initial_quantity * 0.2'))
            ->distinct('ingredient_id')
            ->count('ingredient_id');

        return [
            Stat::make('Stok Menipis', $ingredientCount)
                ->description('Bahan baku dengan stok < 20%')
                ->color($ingredientCount > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
