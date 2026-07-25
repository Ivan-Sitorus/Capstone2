<?php

namespace App\Filament\Resources\MenuResource\Forms;

use App\Filament\Resources\MenuResource;
use App\Models\Ingredient;
use App\Services\MenuImageService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make("name")
                ->label("Nama Menu")
                ->required()
                ->maxLength(255)
                ->live(onBlur: true),
            Select::make("category_id")
                ->label("Kategori Menu")
                ->relationship("category", "name")
                ->required()
                ->searchable()
                ->preload()
                ->placeholder("Pilih Kategori Menu")
                ->createOptionForm([
                    TextInput::make("name")
                        ->label("Kategori Menu")
                        ->required(),
                ])
                ->createOptionAction(fn (Action $action) => $action->label("+ Kategori Baru")),
            FileUpload::make("image")
                ->label("Gambar Menu")
                ->directory("menus/")
                ->disk("public")
                ->imagePreviewHeight("200")
                ->placeholder("Pilih gambar...")
                ->acceptedFileTypes(["image/jpeg", "image/png", "image/webp"])
                ->maxSize(5120)
                ->nullable()
                ->saveUploadedFileUsing(function ($file) {
                    return app(MenuImageService::class)->convertAndStore($file);
                }),
            TextInput::make("price")
                ->label("Harga")
                ->required()
                ->type("text")
                ->minValue(0.01)
                ->stripCharacters(".")
                ->prefix("Rp"),
            Select::make('status')
                ->label('Status')
                ->options([
                    'active' => 'Aktif',
                    'inactive' => 'Nonaktif',
                ])
                ->default('active')
                ->required(),
            TextInput::make("discounted_price")
                ->label("Harga Diskon")
                ->type("text")
                ->minValue(0)
                ->rules([
                    fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                        $price = (int) str_replace(".", "", $get("price") ?? "0");
                        $discountedPrice = (int) str_replace(".", "", $value ?? "0");
                        if ($discountedPrice > $price) {
                            $fail("Harga diskon tidak boleh lebih besar dari harga menu (Rp ".number_format($price, 0, ",", ".").").");
                        }
                    },
                ])
                ->stripCharacters(".")
                ->prefix("Rp")
                ->placeholder("Kosongkan jika tidak ada"),
            Repeater::make("menuIngredients")
                ->relationship("menuIngredients")
                ->label("Resep Menu")
                ->addActionLabel("+ Tambah Bahan")
                ->columnSpanFull()
                ->minItems(1)
                ->required()
                ->schema([
                    Select::make("ingredient_id")
                        ->label("Bahan Baku")
                        ->relationship("ingredient", "name")
                        ->required()
                        ->searchable()
                        ->preload()
                        ->live()
                        ->placeholder("Pilih bahan baku...")
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->name." (".$record->unit.")"),
                    TextInput::make("quantity_used")
                        ->label("Jumlah per Porsi")
                        ->required()
                        ->numeric()
                        ->minValue(0.01)
                        ->step(0.01)
                        ->suffix(fn (Get $get): ?string => $get("ingredient_id")
                            ? " ".(Ingredient::find($get("ingredient_id"))?->unit ?? "")
                            : null),
                    Select::make("unit_id")
                        ->label("Satuan")
                        ->options(fn (Get $get): array => MenuResource::getCompatibleUnitOptions($get("ingredient_id")))
                        ->searchable()
                        ->preload()
                        ->default(fn (Get $get, ?\App\Models\MenuIngredient $record) =>
                            $record ? $record->unit_id : null
                        ),
                ]),
        ]);
    }
}
