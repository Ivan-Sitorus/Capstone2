<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KlasterisasiMenuResource\Pages\ListKlasterisasiMenu;
use App\Filament\Resources\KlasterisasiMenuResource\Pages\ViewKlasterisasiMenu;
use App\Filament\Resources\KlasterisasiMenuResource\Tables\KlasterisasiMenuTable;
use App\Filament\Widgets\ClusteringChartWidget;
use App\Filament\Widgets\ClusteringSummaryWidget;
use App\Models\DataminingRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class KlasterisasiMenuResource extends Resource
{
    public const TYPE = 'clustering';

    protected static ?string $model = DataminingRun::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string | UnitEnum | null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Klasterisasi Menu';

    protected static ?string $pluralLabel = 'Klasterisasi Menu';

    protected static ?string $label = 'Klasterisasi Menu';

    protected static ?string $slug = 'klasterisasi-menu';

    protected static ?int $navigationSort = 16;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('type', self::TYPE);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getResultWidgets(): array
    {
        return [
            ClusteringChartWidget::class,
            ClusteringSummaryWidget::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return KlasterisasiMenuTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKlasterisasiMenu::route('/'),
            'view' => ViewKlasterisasiMenu::route('/{record}'),
        ];
    }
}
