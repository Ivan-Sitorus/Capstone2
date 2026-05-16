<?php

namespace App\Filament\Widgets;

use App\Models\Ingredient;
use App\Models\DailyIngredientUsage;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class InventoryOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 5;

    protected ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $activeIngredients = Ingredient::where('is_active', true)->get();

        $totalActive = $activeIngredients->count();

        $lowStockCount = $activeIngredients->filter(
            fn (Ingredient $ingredient) => $ingredient->getTotalStock() <= $ingredient->low_stock_threshold
        )->count();

        $todayUsage = (float) DailyIngredientUsage::whereDate('usage_date', today())
            ->sum('jumlah_digunakan');

        return [
            Stat::make('Total Bahan Baku', $totalActive)
                ->description('Bahan baku aktif')
                ->descriptionIcon('heroicon-m-beaker')
                ->color('primary'),

            Stat::make('Stok Menipis', $lowStockCount)
                ->description('Di bawah ambang batas')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Penggunaan Hari Ini', number_format($todayUsage, 2, ',', '.'))
                ->description('Total jumlah digunakan')
                ->descriptionIcon('heroicon-m-arrow-down')
                ->color('warning'),
        ];
    }
}
