<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PemakaianBahanBakuWidget;
use App\Filament\Widgets\PiutangStats;
use App\Filament\Widgets\StokKedaluwarsaWidget;
use App\Filament\Widgets\StokMenipisWidget;
use App\Filament\Widgets\TransactionStats;
use App\Filament\Widgets\UtangStats;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            TransactionStats::class,
            PiutangStats::class,
            UtangStats::class,
            StokMenipisWidget::class,
            StokKedaluwarsaWidget::class,
            PemakaianBahanBakuWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 3;
    }
}
