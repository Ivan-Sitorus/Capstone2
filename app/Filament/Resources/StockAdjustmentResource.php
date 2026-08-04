<?php

namespace App\Filament\Resources;

use App\Enums\AdjustableType;
use App\Enums\AdjustmentCategory;
use App\Enums\AdjustmentType;
use App\Filament\Resources\StockAdjustmentResource\Actions\DetailAdjustmentAction;
use App\Filament\Resources\StockAdjustmentResource\Forms\StockAdjustmentForm;
use App\Filament\Resources\StockAdjustmentResource\Pages\ListStockAdjustments;
use App\Filament\Resources\StockAdjustmentResource\RelationManagers\MovementsRelationManager;
use App\Filament\Resources\StockAdjustmentResource\Tables\StockAdjustmentTable;
use App\Models\StockAdjustment;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StockAdjustmentResource extends Resource
{
    protected static ?string $model = StockAdjustment::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static bool $shouldRegisterNavigation = true;

    protected static string | UnitEnum | null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Penyesuaian Stok';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'penyesuaian-stok';

    protected static ?string $breadcrumb = 'Penyesuaian Stok';

    protected static ?string $label = 'Penyesuaian Stok';

    protected static ?string $pluralLabel = 'Penyesuaian Stok';

    public static function getInfolistComponents(string $prefix = ''): array
    {
        $p = $prefix;

        return [
            Section::make('Informasi Penyesuaian')
                ->columns(3)
                ->schema([
                    TextEntry::make($p.'code')->label('Kode Penyesuaian')->copyable(),
                    TextEntry::make($p.'adjusted_at')->label('Waktu')->dateTime('d M Y, H:i:s'),
                    TextEntry::make($p.'adjustment_type')->label('Tipe')
                        ->badge()
                        ->color(fn (AdjustmentType $state): string => $state === AdjustmentType::Increase ? 'success' : 'danger')
                        ->formatStateUsing(fn (AdjustmentType $state): string => $state === AdjustmentType::Increase ? 'Penambahan' : 'Pengurangan'),
                    TextEntry::make($p.'category')->label('Kategori')
                        ->formatStateUsing(fn (?AdjustmentCategory $state): string => $state?->label() ?? '-'),
                    TextEntry::make($p.'adjustable_type')->label('Jenis')
                        ->formatStateUsing(fn (?AdjustableType $state): string => $state === AdjustableType::Ingredient ? 'Bahan Baku' : 'Menu'),
                    TextEntry::make($p.'ingredient.name')->label('Bahan Baku')->default('-'),
                    TextEntry::make($p.'menu.name')->label('Menu')->default('-'),
                    TextEntry::make($p.'quantity')->label('Jumlah')
                        ->formatStateUsing(fn ($state, $record): string => static::formatQty(
                            $state, $record, $p
                        ))
                        ->color(fn ($state, $record): string =>
                            static::getAdjustmentType($record, $p) === AdjustmentType::Decrease ? 'danger' : 'success'),
                    TextEntry::make($p.'quantity_before')->label('Sebelum')
                        ->formatStateUsing(fn ($state, $record): string =>
                            number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.')
                            . ' ' . static::getUnit($record, $p)
                        ),
                    TextEntry::make($p.'quantity_after')->label('Sesudah')
                        ->formatStateUsing(fn ($state, $record): string =>
                            number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.')
                            . ' ' . static::getUnit($record, $p)
                        ),
                    TextEntry::make($p.'reason')->label('Catatan Penyesuaian')->default('-')->columnSpanFull(),
                    TextEntry::make($p.'reportedBy.name')->label('Dilaporkan Oleh')->default('-'),
                ]),
            Section::make('Bahan Baku Terpengaruh')
                ->columnSpanFull()
                ->visible(fn ($record): bool =>
                    static::getAdjustableType($record, $p) === AdjustableType::Menu
                )
                ->schema([
                    RepeatableEntry::make($p.'stockMovements')
                        ->hiddenLabel()
                        ->table([
                            TableColumn::make('Bahan Baku'),
                            TableColumn::make('Perubahan')->width(120),
                            TableColumn::make('Sebelum')->width(100),
                            TableColumn::make('Sesudah')->width(100),
                        ])
                        ->schema([
                            TextEntry::make('ingredient.name'),
                            TextEntry::make('quantity_change')
                                ->formatStateUsing(fn ($state, $record) =>
                                    ((float) $state >= 0 ? '+' : '-')
                                    . number_format(abs((float) $state), abs((float) $state) != (int) abs((float) $state) ? 2 : 0, ',', '.')
                                    . ' ' . ($record->ingredient?->unit ?? '')
                                )
                                ->color(fn ($state): string => (float) $state < 0 ? 'danger' : 'success'),
                            TextEntry::make('quantity_before')
                                ->formatStateUsing(fn ($state, $record) =>
                                    number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.')
                                    . ' ' . ($record->ingredient?->unit ?? '')
                                ),
                            TextEntry::make('quantity_after')
                                ->formatStateUsing(fn ($state, $record) =>
                                    number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.')
                                    . ' ' . ($record->ingredient?->unit ?? '')
                                ),
                        ]),
                ]),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return StockAdjustmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockAdjustmentTable::configure($table);
    }

    private static function resolveRecord($record, string $prefix): mixed
    {
        return $prefix ? $record?->{str_replace('.', '', $prefix)} : $record;
    }

    public static function getAdjustableType($record, string $prefix): ?AdjustableType
    {
        $adj = static::resolveRecord($record, $prefix);
        return $adj?->adjustable_type ?? AdjustableType::Ingredient;
    }

    public static function getAdjustmentType($record, string $prefix): ?AdjustmentType
    {
        $adj = static::resolveRecord($record, $prefix);
        return $adj?->adjustment_type ?? AdjustmentType::Increase;
    }

    public static function getUnit($record, string $prefix): string
    {
        $adj = static::resolveRecord($record, $prefix);
        if (! $adj) {
            return '';
        }
        return method_exists($adj, 'isMenuAdjustment') && $adj->isMenuAdjustment()
            ? 'porsi'
            : ($adj->ingredient?->unit ?? '');
    }

    public static function formatNumber(float $value): string
    {
        return number_format(
            abs($value),
            abs($value) != (int) abs($value) ? 2 : 0,
            ',', '.'
        );
    }

    public static function formatQty($state, $record, string $prefix): string
    {
        $adj = static::resolveRecord($record, $prefix);
        $isIncrease = $adj?->adjustment_type === AdjustmentType::Increase;
        $sign = $isIncrease ? '+' : '-';
        $num = number_format(
            abs((float) $state),
            abs((float) $state) != (int) abs((float) $state) ? 2 : 0,
            ',', '.'
        );
        return $sign . $num . ' ' . static::getUnit($record, $prefix);
    }

    public static function getRelations(): array
    {
        return [
            MovementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockAdjustments::route('/'),
        ];
    }
}
