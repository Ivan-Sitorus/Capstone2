<?php

namespace App\Filament\Resources\StockAdjustmentResource\Forms;

use App\Enums\AdjustableType;
use App\Models\Ingredient;
use App\Models\StockAdjustment;
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
                ->options(StockAdjustment::ADJUSTABLE_TYPES)
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
                    StockAdjustment::TYPE_INCREASE => 'Penambahan',
                    StockAdjustment::TYPE_DECREASE => 'Pengurangan',
                ])
                ->required()
                ->native(false)
                ->live(),
            Select::make('category')
                ->label('Kategori')
                ->options(fn (Get $get) => filled($get('adjustment_type'))
                    ? StockAdjustment::getCategoryOptions($get('adjustment_type'))
                    : StockAdjustment::DECREASE_CATEGORIES + StockAdjustment::INCREASE_CATEGORIES)
                ->required()
                ->disabled(fn (Get $get) => ! filled($get('adjustment_type')))
                ->native(false),
            TextInput::make('quantity')
                ->label('Jumlah')
                ->required()
                ->numeric(fn (Get $get) => $get('adjustable_type') === AdjustableType::Menu->value)
                ->type('text')
                ->mask(\App\Filament\Forms\Components\NumericInput::mask(3))
                ->stripCharacters('.')
                ->maxLength(\App\Filament\Forms\Components\NumericInput::maxLength(6, 3))
                ->mutateStateForValidationUsing(\App\Filament\Forms\Components\NumericInput::validationNormalizer())
                ->dehydrateStateUsing(fn ($state) => \App\Filament\Forms\Components\NumericInput::normalizeState($state))
                ->prefix(fn (Get $get) => $get('adjustment_type') === StockAdjustment::TYPE_DECREASE ? '-' : '+')
                ->suffix(fn (Get $get) => $get('adjustable_type') === AdjustableType::Menu->value
                    ? ' porsi'
                    : ($get('ingredient_id')
                        ? ' ' . (Ingredient::find($get('ingredient_id'))?->unit ?? '')
                        : '')
                ),
            Textarea::make('reason')
                ->label('Catatan')
                ->rows(3)
                ->maxLength(65535),
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
