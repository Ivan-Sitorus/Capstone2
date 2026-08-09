<?php

namespace App\Filament\Resources\StockAdjustmentResource\Forms;

use App\Enums\AdjustmentType;
use App\Filament\Forms\Components\NumericInput;
use App\Models\Ingredient;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('ingredient_id')
                ->label('Bahan')
                ->relationship('ingredient', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->native(false)
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name.' ('.$record->unit?->value.')'),
            Select::make('adjustment_type')
                ->label('Tipe Penyesuaian')
                ->options([
                    AdjustmentType::Increase->value => 'Penambahan',
                    AdjustmentType::Decrease->value => 'Pengurangan',
                ])
                ->required()
                ->native(false)
                ->live(),
            NumericInput::quantity('quantity')
                ->label('Jumlah')
                ->required()
                ->prefix(fn (Get $get) => $get('adjustment_type') === AdjustmentType::Decrease->value ? '-' : '+')
                ->suffix(fn (Get $get) => $get('ingredient_id')
                    ? ' ' . (Ingredient::find($get('ingredient_id'))?->unit?->value ?? '')
                    : ''
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
                ->native(false)
                ->default(fn () => Auth::id()),
            DateTimePicker::make('adjusted_at')
                ->label('Tanggal Kejadian')
                ->seconds(false)
                ->default(now()),
        ]);
    }
}
