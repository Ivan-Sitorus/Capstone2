<?php

namespace App\Filament\Pages;

class KlasterisasiBahanBaku extends AnalyticsPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Klasterisasi Bahan Baku';

    protected static ?int $navigationSort = 14;

    public function getView(): string
    {
        return 'filament.pages.klasterisasi-bahan-baku';
    }

    public function getTitle(): string
    {
        return 'Klasterisasi Bahan Baku';
    }

    protected function getAnalysisType(): string
    {
        return 'ingredient_clustering';
    }

    protected function getFastApiEndpoint(): string
    {
        return '/clustering-bahan-baku';
    }

    protected function getFastApiTimeout(): int
    {
        return 120;
    }
}
