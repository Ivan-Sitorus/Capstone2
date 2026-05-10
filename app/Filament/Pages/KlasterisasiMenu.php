<?php

namespace App\Filament\Pages;

class KlasterisasiMenu extends AnalyticsPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

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
