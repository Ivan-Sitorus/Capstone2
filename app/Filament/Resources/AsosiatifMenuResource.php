<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AsosiatifMenuResource\Pages\ListAsosiatifMenu;
use App\Filament\Resources\AsosiatifMenuResource\Pages\ViewAsosiatifMenu;
use App\Filament\Resources\AsosiatifMenuResource\Tables\AsosiatifMenuTable;
use App\Filament\Widgets\AssociationChartWidget;
use App\Filament\Widgets\AssociationSummaryWidget;
use App\Models\DataminingRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AsosiatifMenuResource extends Resource
{
    public const TYPE = 'association';

    protected static ?string $model = DataminingRun::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-link';

    protected static string | UnitEnum | null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Asosiasi Menu';

    protected static ?string $pluralLabel = 'Asosiasi Menu';

    protected static ?string $label = 'Asosiasi Menu';

    protected static ?string $slug = 'asosiatif-menu';

    protected static ?int $navigationSort = 13;

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
        return true;
    }

    public static function getResultWidgets(): array
    {
        return [
            AssociationChartWidget::class,
            AssociationSummaryWidget::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return AsosiatifMenuTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAsosiatifMenu::route('/'),
            'view' => ViewAsosiatifMenu::route('/{record}'),
        ];
    }
}
