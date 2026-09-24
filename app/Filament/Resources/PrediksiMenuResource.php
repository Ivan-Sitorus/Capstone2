<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrediksiMenuResource\Pages\ListPrediksiMenu;
use App\Filament\Resources\PrediksiMenuResource\Pages\ViewPrediksiMenu;
use App\Filament\Resources\PrediksiMenuResource\Tables\PrediksiMenuTable;
use App\Filament\Widgets\PredictionChartWidget;
use App\Filament\Widgets\PredictionSummaryWidget;
use App\Models\DataminingRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PrediksiMenuResource extends Resource
{
    public const TYPE = 'prediction';

    protected static ?string $model = DataminingRun::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string | UnitEnum | null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Prediksi Menu';

    protected static ?string $pluralLabel = 'Prediksi Menu';

    protected static ?string $label = 'Prediksi Menu';

    protected static ?string $slug = 'prediksi-menu';

    protected static ?int $navigationSort = 14;

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
            PredictionChartWidget::class,
            PredictionSummaryWidget::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return PrediksiMenuTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrediksiMenu::route('/'),
            'view' => ViewPrediksiMenu::route('/{record}'),
        ];
    }
}
