<?php

namespace App\Filament\Pages;

class AsosiatifMenu extends AnalyticsPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-link';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Asosiatif Menu';

    protected static ?int $navigationSort = 13;

    public function getView(): string
    {
        return 'filament.pages.asosiatif-menu';
    }

    public function getTitle(): string
    {
        return 'Asosiatif Menu';
    }

    protected function getAnalysisType(): string
    {
        return 'association';
    }

    protected function getFastApiEndpoint(): string
    {
        return '/association';
    }

    protected function getFastApiTimeout(): int
    {
        return 120;
    }
}
