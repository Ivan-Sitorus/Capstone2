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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
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
                ->maxLength(255),
            TextInput::make('slug')
                ->label('Slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(2)
                ->nullable(),
            Select::make('category_id')
                ->label('Kategori')
                ->relationship('category', 'name')
                ->required()
                ->searchable()
                ->preload(),
            FileUpload::make('image')
                ->label('Gambar Menu')
                ->directory('menus/')
                ->disk('public')
                ->image()
                ->imagePreviewHeight('200')
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
                ->numeric()
                ->minValue(0)
                ->prefix('Rp'),
            TextInput::make('cashback')
                ->label('Cashback Mahasiswa')
                ->numeric()
                ->minValue(0)
                ->default(0)
                ->prefix('Rp'),
            Toggle::make('is_available')
                ->label('Tersedia')
                ->default(true)
                ->inline(false),
            Toggle::make('is_student_discount')
                ->label('Ada Diskon Mahasiswa')
                ->default(true)
                ->inline(false),
            Toggle::make('is_stock_calculated')
                ->label('Stok Otomatis dari Resep')
                ->dehydrated(false)
                ->disabled()
                ->helperText('Nilai ini otomatis aktif jika menu memiliki resep bahan.'),
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
