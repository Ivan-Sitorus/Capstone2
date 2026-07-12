<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class KlasterisasiMenu extends AnalyticsPage
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string | UnitEnum | null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Klasterisasi Menu Penjualan';

    protected static ?int $navigationSort = 11;

    public function getView(): string
    {
        return 'filament.pages.klasterisasi-menu';
    }

    public function getTitle(): string
    {
        return 'Klasterisasi Menu Penjualan';
    }

    protected function getAnalysisType(): string
    {
        return 'menu_clustering';
    }

    protected function getFastApiEndpoint(): string
    {
        return '/clustering';
    }

    protected function getFastApiTimeout(): int
    {
        return 120;
    }
}
