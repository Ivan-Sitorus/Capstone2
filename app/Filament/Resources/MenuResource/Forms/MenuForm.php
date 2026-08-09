<?php

namespace App\Filament\Resources\MenuResource\Forms;

use App\Enums\Unit;
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
                ->native(false)
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
            NumericInput::money("price")
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
                ->native(false)
                ->required(),
            NumericInput::money("discounted_price")
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
                        ->native(false)
                        ->live()
                        ->distinct()
                        ->placeholder("Pilih bahan baku...")
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->name." (".($record->unit?->value ?? '').")")
                        ->suffix(fn (Get $get) => Ingredient::find($get('ingredient_id'))?->unit?->value ?? '')
                        ->afterStateUpdated(function (Set $set, Get $get): void {
                            $ingredientId = $get('ingredient_id');
                            if (! $ingredientId) {
                                $set('unit', null);
                                return;
                            }
                            $ingredient = Ingredient::find($ingredientId);
                            $unit = $ingredient?->unit;

                            // Auto-fill hanya untuk tipe count (pcs/sachet — satu-satunya opsi).
                            // Weight/volume (gram/kg/ml/liter) dibiarkan kosong agar user memilih sendiri.
                            $set('unit', $unit && $unit->unitType() === 'count' ? $unit->value : null);
                        }),
                    Select::make("unit")
                        ->label("Satuan")
                        ->required()
                        ->native(false)
                        ->live()
                        ->options(function (Get $get): array {
                            $ingredientId = $get('ingredient_id');
                            if (! $ingredientId) {
                                return [];
                            }
                            $ingredient = Ingredient::find($ingredientId);
                            if (! $ingredient?->unit) {
                                return [];
                            }

                            $unitType = $ingredient->unit->unitType();

                            return collect(Unit::cases())
                                ->filter(fn (Unit $unit): bool => $unit->unitType() === $unitType)
                                ->mapWithKeys(fn (Unit $unit): array => [$unit->value => $unit->label()])
                                ->all();
                        })
                        ->disabled(function (Get $get): bool {
                            $ingredientId = $get('ingredient_id');
                            if (! $ingredientId) {
                                return false;
                            }
                            $ingredient = Ingredient::find($ingredientId);

                            return $ingredient?->unit?->unitType() === 'count';
                        })
                        ->default(function (Get $get, ?\App\Models\MenuIngredient $record): ?string {
                            if ($record?->unit) {
                                return $record->unit->value;
                            }

                            $ingredientId = $get('ingredient_id');
                            if (! $ingredientId) {
                                return null;
                            }

                            $unit = Ingredient::find($ingredientId)?->unit;

                            // Auto-pilih hanya untuk count; weight/volume biarkan kosong.
                            return $unit && $unit->unitType() === 'count' ? $unit->value : null;
                        }),
                    TextInput::make("quantity_used")
                        ->label("Jumlah per Porsi")
                        ->required()
                        ->numeric()
                        ->type('text')
                        ->mask(fn (Get $get) => NumericInput::mask(
                            NumericInput::precisionForUnit($get('unit')),
                        ))
                        ->stripCharacters('.')
                        ->rules(fn (Get $get) => NumericInput::rulesFor(
                            maxDigits: 6,
                            precision: NumericInput::precisionForUnit($get('unit')),
                        ))
                        ->mutateStateForValidationUsing(fn ($state) => NumericInput::normalizeState($state))
                        ->dehydrateStateUsing(fn ($state) => NumericInput::normalizeState($state))
                        ->disabled(fn (Get $get): bool => blank($get('ingredient_id')))
                        ->minValue(fn (Get $get): float => in_array($get('unit'), ['gram', 'ml'], true) ? 1 : 0.001)
                        ->step(fn (Get $get): float => in_array($get('unit'), ['gram', 'ml'], true) ? 1 : 0.001)
                        ->suffix(fn (Get $get) => $get('unit') ?? ''),
                ]),
        ]);
    }
}
