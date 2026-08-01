<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardStatsWidget;
use App\Filament\Widgets\PemakaianBahanBakuWidget;
use App\Filament\Widgets\PenjualanChartWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            DashboardStatsWidget::class,
            PenjualanChartWidget::class,
            PemakaianBahanBakuWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 4;
    }
}
