<?php

namespace App\Filament\Clusters\Financial;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use UnitEnum;

class FinancialCluster extends Cluster
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string | UnitEnum | null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Laporan Keuangan';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Laporan Keuangan';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
