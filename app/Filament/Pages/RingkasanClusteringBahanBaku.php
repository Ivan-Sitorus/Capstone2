<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ClusteringBahanBakuHistoryWidget;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class RingkasanClusteringBahanBaku extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';

    protected static string|\UnitEnum|null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Ringkasan Klasterisasi Bahan Baku';

    protected static ?string $title = 'Ringkasan Klasterisasi Bahan Baku';

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
            ClusteringBahanBakuHistoryWidget::class,
        ];
    }
}
