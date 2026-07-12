<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class AsosiatifMenu extends AnalyticsPage
{
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedLink;

    protected static string | UnitEnum | null $navigationGroup = 'Analitik';

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
