<?php

namespace App\Filament\Resources;

use App\Filament\Helpers\NumberInputHelper;
use App\Filament\Resources\MenuStockAdjustmentResource\Pages\ListMenuStockAdjustments;
use App\Models\MenuStockAdjustment;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MenuStockAdjustmentResource extends Resource
{
    protected static ?string $model = MenuStockAdjustment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static bool $shouldRegisterNavigation = false;

    protected static string | \UnitEnum | null $navigationGroup = 'Inventori';

    protected static ?string $navigationLabel = 'Penyesuaian Stok Menu';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('menu_stock_id')
                ->label('Produk')
                ->relationship('menuStock', 'id')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->menu->name . ' (' . $record->unit . ')')
                ->required()
                ->searchable()
                ->preload()
                ->disabledOn('edit'),
            Select::make('adjustment_type')
                ->label('Tipe Penyesuaian')
                ->options([
                    MenuStockAdjustment::TYPE_INCREASE => 'Tambah',
                    MenuStockAdjustment::TYPE_DECREASE => 'Kurangi',
                ])
                ->required()
                ->native(false)
                ->live()
                ->disabledOn('edit'),
            TextInput::make('quantity')
                ->label('Jumlah')
                ->required()
                ->disabledOn('edit')
                ->prefix(fn (Get $get) => $get('adjustment_type') === MenuStockAdjustment::TYPE_DECREASE ? '-' : '+')
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
            ->modifyQueryUsing(fn ($query) => $query->with(['menuStock.menu', 'reportedBy']))
            ->columns([
                TextColumn::make('adjusted_at')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('menuStock.menu.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('adjustment_type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => $state === MenuStockAdjustment::TYPE_INCREASE ? 'primary' : 'danger')
                    ->formatStateUsing(fn (string $state): string => $state === MenuStockAdjustment::TYPE_INCREASE ? 'Tambah' : 'Kurangi'),
                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(fn (MenuStockAdjustment $record) => ' ' . ($record->menuStock?->unit ?? '')),
                TextColumn::make('quantity_before')
                    ->label('Sebelum')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('quantity_after')
                    ->label('Sesudah')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(50)
                    ->tooltip(fn (MenuStockAdjustment $record) => $record->reason),
                TextColumn::make('reportedBy.name')
                    ->label('Oleh')
                    ->default('-'),
            ])
            ->filters([
                SelectFilter::make('menu_stock')
                    ->relationship('menuStock.menu', 'name')
                    ->label('Produk'),
                SelectFilter::make('adjustment_type')
                    ->label('Tipe')
                    ->options([
                        MenuStockAdjustment::TYPE_INCREASE => 'Tambah',
                        MenuStockAdjustment::TYPE_DECREASE => 'Kurangi',
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMenuStockAdjustments::route('/'),
        ];
    }
}
