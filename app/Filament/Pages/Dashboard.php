<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * Kept for Livewire component checksum compatibility.
     * Removing HasFiltersForm trait changes component signature;
     * this empty property prevents checksum mismatch errors in SPA mode.
     */
    public array $filters = [];

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\SalesOverview::class,
            \App\Filament\Widgets\DailySalesChart::class,
            \App\Filament\Widgets\TopMenuTable::class,
            \App\Filament\Widgets\InventoryOverview::class,
            \App\Filament\Widgets\CashFlowStatsWidget::class,
            \App\Filament\Widgets\CashFlowChartWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'default' => 4,
            'md' => 4,
            'xl' => 4,
        ];
    }
}
