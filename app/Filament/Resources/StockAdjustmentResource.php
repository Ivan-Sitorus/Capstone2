<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use App\Filament\Helpers\NumberInputHelper;
use App\Filament\Resources\StockAdjustmentResource\RelationManagers\MovementsRelationManager;
use App\Filament\Resources\StockAdjustmentResource\Pages\ListStockAdjustments;
use App\Models\StockAdjustment;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentResource extends Resource
{
    protected static ?string $model = StockAdjustment::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string | \UnitEnum | null $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Stock Adjustments';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('ingredient_id')
                ->label('Bahan')
                ->relationship('ingredient', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->disabledOn('edit')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' (' . $record->unit . ')'),
            Select::make('adjustment_type')
                ->label('Tipe Penyesuaian')
                ->options([
                    StockAdjustment::TYPE_INCREASE => 'Increase',
                    StockAdjustment::TYPE_DECREASE => 'Decrease',
                ])
                ->required()
                ->native(false)
                ->live()
                ->disabledOn('edit'),
            TextInput::make('quantity')
                ->label('Jumlah')
                ->required()
                ->disabledOn('edit')
                ->prefix(fn (Get $get) => $get('adjustment_type') === StockAdjustment::TYPE_DECREASE ? '-' : '+')
                ->extraAttributes(NumberInputHelper::decimal()),
            Textarea::make('reason')
                ->label('Alasan')
                ->required()
                ->rows(3)
                ->maxLength(65535)
                ->disabledOn('edit'),
            Select::make('reported_by')
                ->label('Dilaporkan Oleh')
                ->relationship('reportedBy', 'name')
                ->searchable()
                ->preload()
                ->default(fn () => Auth::id())
                ->disabledOn('edit'),
            DateTimePicker::make('adjusted_at')
                ->label('Tanggal Kejadian')
                ->seconds(false)
                ->default(now())
                ->disabledOn('edit'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['ingredient', 'reportedBy']))
            ->columns([
                TextColumn::make('adjusted_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ingredient.name')
                    ->label('Bahan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('adjustment_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => $state === StockAdjustment::TYPE_INCREASE ? 'primary' : 'danger')
                    ->formatStateUsing(fn (string $state): string => $state === StockAdjustment::TYPE_INCREASE ? 'Increase' : 'Decrease'),
                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(fn (StockAdjustment $record) => ' ' . ($record->ingredient?->unit ?? ''))
                    ->sortable(),
                TextColumn::make('quantity_before')
                    ->label('Sebelum')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('quantity_after')
                    ->label('Sesudah')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(50)
                    ->tooltip(fn (StockAdjustment $record) => $record->reason),
                TextColumn::make('reportedBy.name')
                    ->label('Dilaporkan Oleh')
                    ->default('-')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('ingredient')
                    ->relationship('ingredient', 'name')
                    ->label('Bahan'),
                SelectFilter::make('adjustment_type')
                    ->label('Tipe')
                    ->options([
                        StockAdjustment::TYPE_INCREASE => 'Increase',
                        StockAdjustment::TYPE_DECREASE => 'Decrease',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->label('Detail')->modal(),
            ])
            ->toolbarActions([])
            ->defaultSort('adjusted_at', 'desc');
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
