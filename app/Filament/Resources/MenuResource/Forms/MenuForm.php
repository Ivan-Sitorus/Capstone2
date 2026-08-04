<?php

namespace App\Filament\Resources\MenuResource\Forms;

use App\Filament\Forms\Components\NumericInput;
use App\Filament\Resources\MenuResource;
use App\Models\Ingredient;
use App\Services\MenuImageService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                        ->required()
                        ->maxLength(255),
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
            NumericInput::apply(TextInput::make("price"), maxDigits: 9)
                ->label("Harga")
                ->required()
                ->integer()
                ->minValue(1)
                ->prefix("Rp"),
            Select::make('status')
                ->label('Status')
                ->options([
                    'active' => 'Aktif',
                    'inactive' => 'Nonaktif',
                ])
                ->default('active')
                ->required(),
            NumericInput::apply(TextInput::make("discounted_price"), maxDigits: 9)
                ->label("Harga Diskon")
                ->integer()
                ->minValue(0)
                ->rules([
                    fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                        $price = (int) str_replace('.', '', (string) ($get("price") ?? 0));
                        $discountedPrice = (int) str_replace('.', '', (string) ($value ?? 0));
                        if ($discountedPrice > $price) {
                            $fail("Harga diskon tidak boleh lebih besar dari harga menu (Rp ".number_format($price, 0, ",", ".").").");
                        }
                    },
                ])
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
                    NumericInput::apply(TextInput::make("quantity_used"), maxDigits: 6, precision: 3)
                        ->label("Jumlah per Porsi")
                        ->required()
                        ->minValue(0.001)
                        ->maxValue(999999)
                        ->step(0.001),
                ]),
        ]);
    }
}
