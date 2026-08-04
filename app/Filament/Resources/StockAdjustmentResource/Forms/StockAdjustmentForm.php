<?php

namespace App\Filament\Resources\StockAdjustmentResource\Forms;

use App\Enums\AdjustableType;
use App\Enums\AdjustmentCategory;
use App\Enums\AdjustmentType;
use App\Filament\Forms\Components\NumericInput;
use App\Models\Ingredient;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('adjustable_type')
                ->label('Jenis')
                ->options([
                    AdjustableType::Ingredient->value => 'Bahan Baku',
                    AdjustableType::Menu->value => 'Menu',
                ])
                ->default(AdjustableType::Ingredient->value)
                ->required()
                ->native(false)
                ->live(),
            Select::make('ingredient_id')
                ->label('Bahan')
                ->relationship('ingredient', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->visible(fn (Get $get) => $get('adjustable_type') === AdjustableType::Ingredient->value)
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' ('.$record->unit.')'),
            Select::make('menu_id')
                ->label('Menu')
                ->relationship('menu', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->visible(fn (Get $get) => $get('adjustable_type') === AdjustableType::Menu->value),
            Select::make('adjustment_type')
                ->label('Tipe Penyesuaian')
                ->options([
                    AdjustmentType::Increase->value => 'Penambahan',
                    AdjustmentType::Decrease->value => 'Pengurangan',
                ])
                ->required()
                ->native(false)
                ->live(),
            Select::make('category')
                ->label('Kategori')
                ->options(fn (Get $get) => filled($get('adjustment_type'))
                    ? \App\Models\StockAdjustment::getCategoryOptions($get('adjustment_type'))
                    : collect(AdjustmentCategory::cases())
                        ->mapWithKeys(fn (AdjustmentCategory $c) => [$c->value => $c->label()])
                        ->toArray())
                ->required()
                ->disabled(fn (Get $get) => ! filled($get('adjustment_type')))
                ->native(false),
            NumericInput::apply(TextInput::make('quantity'), maxDigits: 6, precision: 3)
                ->label('Jumlah')
                ->required()
                ->prefix(fn (Get $get) => $get('adjustment_type') === AdjustmentType::Decrease->value ? '-' : '+')
                ->suffix(fn (Get $get) => $get('adjustable_type') === AdjustableType::Menu->value
                    ? ' porsi'
                    : ($get('ingredient_id')
                        ? ' ' . (Ingredient::find($get('ingredient_id'))?->unit ?? '')
                        : '')
                ),
            Textarea::make('reason')
                ->label('Catatan')
                ->rows(3)
                ->maxLength(255),
            Select::make('reported_by')
                ->label('Dilaporkan Oleh')
                ->relationship('reportedBy', 'name')
                ->searchable()
                ->preload()
                ->default(fn () => Auth::id()),
            DateTimePicker::make('adjusted_at')
                ->label('Tanggal Kejadian')
                ->seconds(false)
                ->default(now()),
        ]);
    }
}
