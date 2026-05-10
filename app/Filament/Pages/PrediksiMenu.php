<?php

namespace App\Filament\Pages;

class PrediksiMenu extends AnalyticsPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Prediksi Menu';

    protected static ?int $navigationSort = 12;

    public function getView(): string
    {
        return 'filament.pages.prediksi-menu';
    }

    public function getTitle(): string
    {
        return 'Prediksi Menu';
    }

    protected function getAnalysisType(): string
    {
        return 'menu_prediction';
    }

    protected function getFastApiEndpoint(): string
    {
        return '/prediction';
    }

    protected function getFastApiTimeout(): int
    {
        return 600;
    }
}
