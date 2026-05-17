<?php

namespace App\Filament\Resources;

use App\Filament\Helpers\NumberInputHelper;
use App\Filament\Resources\MenuStockResource\Pages\EditMenuStock;
use App\Filament\Resources\MenuStockResource\Pages\ListMenuStocks;
use App\Filament\Resources\MenuStockResource\Pages\ManageMenuStockBatches;
use App\Filament\Resources\MenuStockResource\RelationManagers\MenuStockBatchesRelationManager;
use App\Models\MenuStock;
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

class MenuStockResource extends Resource
{
    protected static ?string $model = MenuStock::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\UnitEnum|null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Stok Menu';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('menu_id')
                    ->label('Menu')
                    ->relationship(
                        name: 'menu',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($q) => $q->where('is_stock_calculated', false),
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('unit')
                    ->label('Unit')
                    ->options(MenuStock::UNITS)
                    ->default('pcs')
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->live(),
                TextInput::make('low_stock_threshold')
                    ->label('Batas Stok Rendah')
                    ->required()
                    ->type('text')
                    ->extraInputAttributes(NumberInputHelper::decimal())
                    ->stripCharacters('.')
                    ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                    ->suffix(fn ($get) => $get('unit') ? ' '.$get('unit') : ''),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->inline(false),
                Radio::make('batch_mode')
                    ->label('Mode Pengambilan Batch')
                    ->options(array_slice(MenuStock::batchModes(), 0, 2))
                    ->default(MenuStock::BATCH_MODE_FEFO)
                    ->required()
                    ->inline(false)
                    ->helperText('FEFO: kadaluarsa terdekat digunakan dulu. FIFO: diterima lebih dulu digunakan dulu.'),
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
                            ->type('text')
                            ->stripCharacters('.')
                            ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                            ->extraInputAttributes(NumberInputHelper::decimal())
                            ->suffix(fn ($get) => $get('../../unit') ? ' '.$get('../../unit') : ''),
                        DatePicker::make('expiry_date')
                            ->label('Tanggal Kadaluarsa')
                            ->nullable()
                            ->native(false)
                            ->required(fn ($get) => $get('../../batch_mode') === MenuStock::BATCH_MODE_FEFO)
                            ->helperText(fn ($get) => $get('../../batch_mode') === MenuStock::BATCH_MODE_FEFO
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
                            ->prefix('Rp'),
                    ])
                    ->defaultItems(0)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('menu.name')
                    ->label('Menu')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('Unit')
                    ->sortable(),
                TextColumn::make('low_stock_threshold')
                    ->label('Batas Stok')
                    ->suffix(fn (MenuStock $record) => ' '.$record->unit)
                    ->sortable(),
                TextColumn::make('total_stock')
                    ->label('Total Stok')
                    ->getStateUsing(fn (MenuStock $record) => $record->getTotalStock())
                    ->suffix(fn (MenuStock $record) => ' '.$record->unit)
                    ->badge()
                    ->color(fn (MenuStock $record) => $record->getTotalStock() < (float) $record->low_stock_threshold ? 'danger' : 'success'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('batch_mode')
                    ->label('Mode')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('low_stock')
                    ->label('Stok Rendah')
                    ->query(fn ($query) => $query->whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM menu_stock_batches WHERE menu_stock_batches.menu_stock_id = menu_stocks.id) < low_stock_threshold')),
                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif saja')
                    ->falseLabel('Non-aktif saja'),
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
        return [
            MenuStockBatchesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMenuStocks::route('/'),
            'edit' => EditMenuStock::route('/{record}/edit'),
            'batches' => ManageMenuStockBatches::route('/{record}/batches'),
        ];
    }
}
