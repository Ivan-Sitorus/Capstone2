<?php

namespace App\Filament\Clusters\Financial;

use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;

class FinancialCluster extends Cluster
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static \UnitEnum|string|null $navigationGroup = 'Detail Keuangan';

    protected static ?string $navigationLabel = 'Laporan Keuangan';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Laporan Keuangan';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
