<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockAdjustmentResource\Actions\CancelAdjustmentAction;
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
use Filament\Tables\Table;

class StockAdjustmentResource extends Resource
{
    protected static ?string $model = StockAdjustment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static bool $shouldRegisterNavigation = true;

    protected static string|\UnitEnum|null $navigationGroup = 'Inventori';

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
                        ->color(fn (string $state): string => $state === 'increase' ? 'success' : 'danger')
                        ->formatStateUsing(fn (string $state): string => $state === 'increase' ? 'Penambahan' : 'Pengurangan'),
                    TextEntry::make($p.'status')->label('Status')
                        ->badge()
                        ->color(fn (?string $state): string => $state === 'cancelled' ? 'danger' : 'success')
                        ->formatStateUsing(fn (?string $state): string => $state === 'cancelled' ? 'Dibatalkan' : 'Aktif'),
                    TextEntry::make($p.'category')->label('Kategori')
                        ->formatStateUsing(fn ($state) => StockAdjustment::DECREASE_CATEGORIES[$state]
                            ?? StockAdjustment::INCREASE_CATEGORIES[$state]
                            ?? $state),
                    TextEntry::make($p.'adjustable_type')->label('Jenis')
                        ->formatStateUsing(fn ($state) => StockAdjustment::ADJUSTABLE_TYPES[$state] ?? $state),
                    TextEntry::make($p.'ingredient.name')->label('Bahan Baku')->default('-'),
                    TextEntry::make($p.'menu.name')->label('Menu')->default('-'),
                    TextEntry::make($p.'quantity')->label('Jumlah')
                        ->formatStateUsing(fn ($state, $record): string => static::formatQty(
                            $state, $record, $p
                        ))
                        ->color(fn ($state, $record): string =>
                            static::getAdjustmentType($record, $p) === 'decrease' ? 'danger' : 'success'),
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
                    TextEntry::make($p.'cancel_reason')->label('Alasan Dibatalkan')
                        ->default('-')
                        ->columnSpanFull()
                        ->visible(fn (?string $state): bool => filled($state)),
                    TextEntry::make($p.'reportedBy.name')->label('Dilaporkan Oleh')->default('-'),
                ]),
            Section::make('Bahan Baku Terpengaruh')
                ->columnSpanFull()
                ->visible(fn ($record): bool =>
                    static::getAdjustableType($record, $p) === 'menu'
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

    public static function getAdjustableType($record, string $prefix): string
    {
        $adj = static::resolveRecord($record, $prefix);
        return $adj?->adjustable_type ?? 'ingredient';
    }

    public static function getAdjustmentType($record, string $prefix): string
    {
        $adj = static::resolveRecord($record, $prefix);
        return $adj?->adjustment_type ?? 'increase';
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
        $isIncrease = $adj?->adjustment_type === StockAdjustment::TYPE_INCREASE;
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
