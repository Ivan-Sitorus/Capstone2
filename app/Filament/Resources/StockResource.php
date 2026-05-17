<?php

namespace App\Filament\Resources;

use App\Filament\Helpers\NumberInputHelper;
use App\Filament\Helpers\TextInputHelper;
use App\Filament\Resources\StockResource\Pages\EditStock;
use App\Filament\Resources\StockResource\Pages\ListStocks;
use App\Filament\Resources\StockResource\Pages\ManageBatches;
use App\Models\Ingredient;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class StockResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|\UnitEnum|null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Stok';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Ingredient Name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->extraInputAttributes(TextInputHelper::string()),
                Select::make('unit')
                    ->label('Unit')
                    ->options(Ingredient::UNITS)
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->live(),
                TextInput::make('low_stock_threshold')
                    ->label('Low Stock Threshold')
                    ->required()
                    ->type('text')
                    ->extraInputAttributes(NumberInputHelper::decimal())
                    ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2, ',', '.') : '')
                    ->stripCharacters('.')
                    ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                    ->suffix(fn ($get) => $get('unit') ? ' '.$get('unit') : ''),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->inline(false),
                Radio::make('batch_mode')
                    ->label('Mode Pengambilan Batch')
                    ->options(array_slice(Ingredient::batchModes(), 0, 2))
                    ->default(Ingredient::BATCH_MODE_FEFO)
                    ->required()
                    ->inline(false)
                    ->helperText('FEFO: batch dengan tanggal kadaluarsa terdekat digunakan lebih dulu. FIFO: batch yang diterima lebih dulu digunakan lebih dulu.'),
                Repeater::make('batches')
                    ->relationship('batches')
                    ->label('Stok Awal (Batch)')
                    ->addActionLabel('+ Tambah Batch')
                    ->hiddenOn('edit')
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->required()
                            ->minValue(0)
                            ->step(0.1)
                            ->type('text')
                            ->stripCharacters('.')
                            ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                            ->extraInputAttributes(NumberInputHelper::decimal())
                            ->suffix(fn ($get) => $get('../../unit') ? ' '.$get('../../unit') : ''),
                        DatePicker::make('expiry_date')
                            ->label('Tanggal Kadaluarsa')
                            ->nullable()
                            ->native(false)
                            ->required(fn ($get) => $get('../../batch_mode') === Ingredient::BATCH_MODE_FEFO)
                            ->helperText(fn ($get) => $get('../../batch_mode') === Ingredient::BATCH_MODE_FEFO
                                ? 'Wajib diisi untuk mode FEFO'
                                : null),
                        DateTimePicker::make('received_at')
                            ->label('Diterima Tanggal')
                            ->nullable()
                            ->default(now())
                            ->native(false),
                        TextInput::make('cost_per_unit')
                            ->label('Harga per Unit')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->type('text')
                            ->stripCharacters('.')
                            ->extraInputAttributes(NumberInputHelper::integer())
                            ->prefix(fn ($get) => $get('../../unit') ? 'Rp/'.$get('../../unit') : 'Rp'),
                    ])
                    ->defaultItems(0)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ingredient Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('Unit')
                    ->sortable(),
                TextColumn::make('low_stock_threshold')
                    ->label('Low Stock Threshold')
                    ->numeric(decimalPlaces: 2, decimalSeparator: ',', thousandsSeparator: '.')
                    ->suffix(fn (Ingredient $record) => ' '.$record->unit)
                    ->sortable(),
                TextColumn::make('total_stock')
                    ->label('Total Stock')
                    ->getStateUsing(fn (Ingredient $record) => $record->getTotalStock() + 0)
                    ->suffix(fn (Ingredient $record) => ' '.$record->unit)
                    ->badge()
                    ->color(fn (Ingredient $record) => $record->getTotalStock() < (float) $record->low_stock_threshold ? 'danger' : 'success')
                    ->sortable(query: function ($query, string $direction): void {
                        $query->orderByRaw('(SELECT COALESCE(SUM(quantity), 0) FROM ingredient_batches WHERE ingredient_batches.ingredient_id = ingredients.id) '.$direction);
                    }),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('low_stock')
                    ->label('Low Stock')
                    ->query(fn ($query) => $query->whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM ingredient_batches WHERE ingredient_batches.ingredient_id = ingredients.id) < low_stock_threshold')),
                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->recordActions([
                Action::make('batches')
                    ->label('Batch')
                    ->icon('heroicon-o-cube')
                    ->url(fn ($record) => static::getUrl('batches', ['record' => $record])),
                EditAction::make()->modal(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStocks::route('/'),
            'edit' => EditStock::route('/{record}/edit'),
            'batches' => ManageBatches::route('/{record}/batches'),
        ];
    }
}
