<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DataMiningOverviewWidget;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class DataMining extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Data Mining';

    protected static ?string $title = 'Data Mining';

    protected static ?int $navigationSort = 9;

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema(fn (): array => $this->getWidgetsSchemaComponents($this->getWidgets())),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            DataMiningOverviewWidget::class,
        ];
    }
}
