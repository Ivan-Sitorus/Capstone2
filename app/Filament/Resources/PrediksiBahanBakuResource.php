<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrediksiBahanBakuResource\Pages\ListPrediksiBahanBaku;
use App\Filament\Resources\PrediksiBahanBakuResource\Pages\ViewPrediksiBahanBaku;
use App\Filament\Resources\PrediksiBahanBakuResource\Tables\PrediksiBahanBakuTable;
use App\Filament\Widgets\PredictionBahanBakuChartWidget;
use App\Filament\Widgets\PredictionBahanBakuSummaryWidget;
use App\Models\DataminingRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PrediksiBahanBakuResource extends Resource
{
    public const TYPE = 'prediction-bahan-baku';

    protected static ?string $model = DataminingRun::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static string | UnitEnum | null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Prediksi Bahan Baku';

    protected static ?string $pluralLabel = 'Prediksi Bahan Baku';

    protected static ?string $label = 'Prediksi Bahan Baku';

    protected static ?string $slug = 'prediksi-bahan-baku';

    protected static ?int $navigationSort = 15;

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
            PredictionBahanBakuChartWidget::class,
            PredictionBahanBakuSummaryWidget::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return PrediksiBahanBakuTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrediksiBahanBaku::route('/'),
            'view' => ViewPrediksiBahanBaku::route('/{record}'),
        ];
    }
}
