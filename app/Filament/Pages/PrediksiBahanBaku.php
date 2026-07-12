<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class PrediksiBahanBaku extends AnalyticsPage
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static string | UnitEnum | null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Prediksi Bahan Baku';

    protected static ?int $navigationSort = 15;

    public function getView(): string
    {
        return 'filament.pages.prediksi-bahan-baku';
    }

    public function getTitle(): string
    {
        return 'Prediksi Penggunaan Bahan Baku';
    }

    protected function getAnalysisType(): string
    {
        return 'ingredient_prediction';
    }

    protected function getFastApiEndpoint(): string
    {
        return '/prediction-bahan-baku';
    }

    protected function getFastApiTimeout(): int
    {
        return 600;
    }
}
