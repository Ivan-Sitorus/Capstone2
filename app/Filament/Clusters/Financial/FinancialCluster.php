<?php

namespace App\Filament\Clusters\Financial;

use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;

class FinancialCluster extends Cluster
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static \UnitEnum|string|null $navigationGroup = 'Finance Details';

    protected static ?string $navigationLabel = 'Financial Reports';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Financial Reports';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
}
