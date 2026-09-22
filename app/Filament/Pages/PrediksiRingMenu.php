<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PredictionHistoryWidget;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class PrediksiRingMenu extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Prediksi Ring Menu';

    protected static ?string $title = 'Prediksi Ring Menu';

    protected static ?int $navigationSort = 10;

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
            PredictionHistoryWidget::class,
        ];
    }
}
