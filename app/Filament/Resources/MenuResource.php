<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\MenuResource\RelationManagers\IngredientsRelationManager;
use App\Filament\Resources\MenuResource\Pages\ListMenus;
use App\Filament\Resources\MenuResource\Pages\EditMenu;
use App\Filament\Resources\MenuResource\Pages;
use App\Filament\Resources\MenuResource\RelationManagers;
use App\Models\Menu;
use App\Filament\Helpers\NumberInputHelper;
use App\Filament\Helpers\TextInputHelper;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use App\Services\MenuImageService;
use Filament\Tables;
use Filament\Tables\Table;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Menu';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Menu')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, Set $set) => $set('slug', \Illuminate\Support\Str::slug($state)))
                ->extraInputAttributes(TextInputHelper::string()),
            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->extraInputAttributes(TextInputHelper::string()),
            Select::make('category_id')
                ->label('Kategori')
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                ->createOptionForm([
                    TextInput::make('name')
                        ->label('Nama Kategori')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, Set $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(ignoreRecord: true),
                ])
                ->createOptionAction(fn (\Filament\Actions\Action $action) => $action->label('+ Kategori Baru')),
            FileUpload::make('image')
                ->label('Gambar Menu')
                ->directory('menus/')
                ->disk('public')
                ->fetchFileInformation(false)
                ->imagePreviewHeight('200')
                ->loadingIndicator()
                ->placeholder('Pilih gambar...')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(5120)
                ->nullable()
                ->saveUploadedFileUsing(function ($file) {
                    $oldPath = $this->record?->image;
                    return app(MenuImageService::class)->convertAndStore($file, $oldPath);
                }),
            TextInput::make('price')
                ->label('Harga Normal')
                ->required()
                ->type('text')
                ->minValue(0)
                ->prefix('Rp')
                ->stripCharacters('.')
                ->extraInputAttributes(NumberInputHelper::decimal())
                ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2, ',', '.') : ''),
            Toggle::make('is_student_discount')
                ->label('Ada Diskon Mahasiswa')
                ->default(true)
                ->inline(false)
                ->live(),
            TextInput::make('cashback')
                ->label('Cashback Mahasiswa')
                ->type('text')
                ->minValue(0)
                ->default(0)
                ->prefix('Rp')
                ->stripCharacters('.')
                ->extraInputAttributes(NumberInputHelper::decimal())
                ->disabled(fn (Get $get) => ! $get('is_student_discount'))
                ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2, ',', '.') : ''),
            Toggle::make('is_available')
                ->label('Tersedia')
                ->default(true)
                ->inline(false),
            Toggle::make('is_stock_calculated')
                ->label('Ada Resep Menu')
                ->live()
                ->afterStateUpdated(function ($state, $record) {
                    if (! $state && $record && $record->exists) {
                        $record->menuIngredients()->delete();
                    }
                }),
            Repeater::make('menuIngredients')
                ->relationship('menuIngredients')
                ->label('Bahan Menu')
                ->addActionLabel('+ Tambah Bahan')
                ->visible(fn (Get $get) => $get('is_stock_calculated'))
                ->columnSpanFull()
                ->itemLabel(function (array $state): string {
                    $name = $state['ingredient_id'] ?? null;
                    if (! $name) {
                        return 'Bahan Baru';
                    }
                    $ingredient = \App\Models\Ingredient::find($name);
                    return $ingredient ? $ingredient->name . ' (' . $ingredient->unit . ')' : 'Bahan Baru';
                })
                ->schema([
                    Select::make('ingredient_id')
                        ->label('Bahan')
                        ->relationship('ingredient', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live()
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->name . ' (' . $record->unit . ')'),
                    TextInput::make('quantity_used')
                        ->label('Jumlah per Porsi')
                        ->required()
                        ->type('text')
                        ->minValue(0.01)
                        ->stripCharacters('.')
                        ->extraInputAttributes(NumberInputHelper::decimal())
                        ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                        ->suffix(function ($state, $record) {
                            if ($record && $record->ingredient_id) {
                                $ingredient = \App\Models\Ingredient::find($record->ingredient_id);
                                return $ingredient ? ' ' . $ingredient->unit : '';
                            }
                            return '';
                        }),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Menu')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('cashback')
                    ->label('Cashback')
                    ->money('IDR')
                    ->sortable(),
                IconColumn::make('is_available')
                    ->label('Tersedia')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_student_discount')
                    ->label('Diskon Mhs')
                    ->boolean(),
                TextColumn::make('is_stock_calculated')
                    ->label('Resep')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Ada Resep' : 'Tanpa Resep')
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->label('Kategori'),
                TernaryFilter::make('is_available')
                    ->label('Tersedia')
                    ->placeholder('Semua')
                    ->trueLabel('Tersedia')
                    ->falseLabel('Tidak Tersedia'),
                TernaryFilter::make('is_stock_calculated')
                    ->label('Resep')
                    ->placeholder('Semua')
                    ->trueLabel('Ada Resep')
                    ->falseLabel('Tanpa Resep'),
            ])
            ->recordActions([
                EditAction::make()->modal(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            IngredientsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListMenus::route('/'),
            'edit'   => EditMenu::route('/{record}/edit'),
        ];
    }
}
