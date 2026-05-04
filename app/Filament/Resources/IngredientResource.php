<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\RawJs;
use App\Filament\Resources\IngredientResource\Pages\ListIngredients;
use App\Filament\Resources\IngredientResource\Pages\EditIngredient;
use App\Filament\Resources\IngredientResource\Pages\ManageBatches;
use App\Models\Ingredient;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IngredientResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cube';

    protected static string | \UnitEnum | null $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Ingredients';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Ingredient Name')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),
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
                ->integer()
                ->minValue(0)
                ->default(0)
                ->extraInputAttributes([
                    'min' => '0',
                    'onkeydown' => "return !(event.key.length===1&&!/[0-9]/.test(event.key))",
                ])
                ->suffix(fn ($get) => $get('unit') ? ' ' . $get('unit') : ''),
            Toggle::make('is_active')
                ->label('Active')
                ->default(true)
                ->inline(false),
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
                        ->numeric()
                        ->minValue(0)
                        ->step(0.1)
                        ->extraInputAttributes([
                            'min' => '0',
                            'onkeydown' => "return !(event.key.length===1&&!/[0-9.]/.test(event.key))"
                        ])
                        ->suffix(fn ($get) => $get('../../unit') ? ' ' . $get('../../unit') : ''),
                    DatePicker::make('expiry_date')
                        ->label('Tanggal Kadaluarsa')
                        ->nullable()
                        ->native(false),
                    DateTimePicker::make('received_at')
                        ->label('Diterima Tanggal')
                        ->required()
                        ->default(now())
                        ->native(false),
                    TextInput::make('cost_per_unit')
                        ->label('Harga per Unit')
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->stripCharacters('.')
                        ->mask(RawJs::make('$money($input, ",", ".", 0)'))
                        ->extraInputAttributes([
                            'min' => '0',
                            'onkeydown' => "return !(event.key.length===1&&!/[0-9]/.test(event.key))",
                        ])
                        ->prefix(fn ($get) => $get('../../unit') ? 'Rp/' . $get('../../unit') : 'Rp'),
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
                    ->numeric(decimalPlaces: 0)
                    ->suffix(fn (Ingredient $record) => ' ' . $record->unit)
                    ->sortable(),
                TextColumn::make('total_stock')
                    ->label('Total Stock')
                    ->getStateUsing(fn (Ingredient $record) => number_format($record->getTotalStock(), 2))
                    ->suffix(fn (Ingredient $record) => ' ' . $record->unit)
                    ->badge()
                    ->color(fn (Ingredient $record) => $record->getTotalStock() < (int) $record->low_stock_threshold ? 'danger' : 'success')
                    ->sortable(query: function ($query, string $direction): void {
                        $query->orderByRaw('(SELECT COALESCE(SUM(quantity), 0) FROM ingredient_batches WHERE ingredient_batches.ingredient_id = ingredients.id) ' . $direction);
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
                EditAction::make(),
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
            'index' => ListIngredients::route('/'),
            'edit' => EditIngredient::route('/{record}/edit'),
            'batches' => ManageBatches::route('/{record}/batches'),
        ];
    }
}
