<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PredictionBahanBakuHistoryWidget;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class PredictionRingBahanBaku extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Prediction Ring Bahan Baku';

    protected static ?string $title = 'Prediction Ring Bahan Baku';

    protected static ?int $navigationSort = 15;

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
            PredictionBahanBakuHistoryWidget::class,
        ];
    }
}
