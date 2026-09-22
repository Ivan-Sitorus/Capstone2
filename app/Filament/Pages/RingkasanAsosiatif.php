<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AssociationHistoryWidget;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class RingkasanAsosiatif extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Ringkasan Asosiatif';

    protected static ?string $title = 'Ringkasan Asosiatif';

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
            AssociationHistoryWidget::class,
        ];
    }
}
