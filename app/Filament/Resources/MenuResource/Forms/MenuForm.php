<?php

namespace App\Filament\Resources\MenuResource\Forms;

use App\Filament\Forms\Components\NumericInput;
use App\Filament\Resources\MenuResource;
use App\Models\Ingredient;
use App\Models\Unit;
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
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->name." (".$record->unit.")")
                        ->afterStateUpdated(function (Set $set, Get $get): void {
                            $ingredientId = $get('ingredient_id');
                            if (! $ingredientId) {
                                $set('unit_id', null);
                                return;
                            }
                            $ingredient = Ingredient::find($ingredientId);
                            if (! $ingredient) {
                                $set('unit_id', null);
                                return;
                            }
                            // Auto-select the ingredient's native unit (count/weight/volume)
                            $unit = \App\Models\Unit::where('name', $ingredient->unit)->first();
                            $set('unit_id', $unit?->id ?? null);
                        }),
                    Select::make("unit_id")
                        ->label("Satuan")
                        ->required()
                        ->dehydrated()
                        ->options(function (Get $get): array {
                            $ingredientId = $get('ingredient_id');
                            if (! $ingredientId) {
                                return [];
                            }

                            $ingredient = Ingredient::find($ingredientId);
                            if (! $ingredient) {
                                return [];
                            }

                        $unitType = \App\Models\Unit::where('name', $ingredient->unit)->value('unit_type');
                        if (!$unitType) return [];
                        
                        // For count type (butir, pcs, sachet, buah) → only the ingredient's own unit
                        if ($unitType === 'count') {
                            $unit = \App\Models\Unit::where('name', $ingredient->unit)->first();
                            return $unit ? [$unit->id => $unit->abbreviation] : [];
                        }
                        
                        // For weight/volume → show compatible units
                        return \App\Models\Unit::where('unit_type', $unitType)
                            ->pluck('abbreviation', 'id')
                            ->toArray();
                        })
                        ->disabled(function (Get $get): bool {
                            $ingredientId = $get('ingredient_id');
                            if (! $ingredientId) {
                                return false;
                            }
                            $ingredient = Ingredient::find($ingredientId);
                            if (! $ingredient) {
                                return false;
                            }
                            $unit = \App\Models\Unit::where('name', $ingredient->unit)->first();
                            return $unit && $unit->unit_type === 'count';
                        })
                        ->default(function (Get $get, ?\App\Models\MenuIngredient $record) {
                            if ($record && $record->unit_id) {
                                return $record->unit_id;
                            }
                            $ingredientId = $get('ingredient_id');
                            if (! $ingredientId) {
                                return null;
                            }
                            $ingredient = Ingredient::find($ingredientId);
                            if (! $ingredient) {
                                return null;
                            }

                            // Auto-select the ingredient's native unit for count/weight/volume
                            $unit = \App\Models\Unit::where('name', $ingredient->unit)->first();
                            return $unit?->id ?? null;
                        })
                        ->searchable()
                        ->preload()
                        ->live(),
                    NumericInput::apply(TextInput::make("quantity_used"), maxDigits: 6, precision: 3)
                        ->label("Jumlah per Porsi")
                        ->disabled(fn (Get $get): bool => ! $get('unit_id'))
                        ->required()
                        ->minValue(fn (Get $get): float => in_array(Unit::find($get('unit_id'))?->name, ['gram', 'ml']) ? 1 : 0.001)
                        ->maxValue(999999)
                        ->step(fn (Get $get): float => in_array(Unit::find($get('unit_id'))?->name, ['gram', 'ml']) ? 1 : 0.001)
                        ->suffix(function (Get $get): ?string {
                            $unitId = $get('unit_id');
                            if (! $unitId) {
                                return null;
                            }
                            return Unit::find($unitId)?->abbreviation ?? null;
                        }),
                ]),
        ]);
    }
}
