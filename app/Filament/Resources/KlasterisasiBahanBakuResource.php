<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KlasterisasiBahanBakuResource\Pages\ListKlasterisasiBahanBaku;
use App\Filament\Resources\KlasterisasiBahanBakuResource\Pages\ViewKlasterisasiBahanBaku;
use App\Filament\Resources\KlasterisasiBahanBakuResource\Tables\KlasterisasiBahanBakuTable;
use App\Filament\Widgets\ClusteringBahanBakuChartWidget;
use App\Filament\Widgets\ClusteringBahanBakuSummaryWidget;
use App\Models\DataminingRun;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class KlasterisasiBahanBakuResource extends Resource
{
    public const TYPE = 'clustering-bahan-baku';

    protected static ?string $model = DataminingRun::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-group';

    protected static string | UnitEnum | null $navigationGroup = 'Analitik';

    protected static ?string $navigationLabel = 'Klasterisasi Bahan Baku';

    protected static ?string $pluralLabel = 'Klasterisasi Bahan Baku';

    protected static ?string $label = 'Klasterisasi Bahan Baku';

    protected static ?string $slug = 'klasterisasi-bahan-baku';

    protected static ?int $navigationSort = 17;

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
            ClusteringBahanBakuChartWidget::class,
            ClusteringBahanBakuSummaryWidget::class,
        ];
    }

    public static function table(Table $table): Table
    {
        return KlasterisasiBahanBakuTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKlasterisasiBahanBaku::route('/'),
            'view' => ViewKlasterisasiBahanBaku::route('/{record}'),
        ];
    }
}
