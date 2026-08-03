<?php

namespace App\Filament\Resources\StockResource\Forms;

use App\Enums\BatchMode;
use App\Models\Ingredient;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class StockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Bahan')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('unit')
                    ->label('Unit')
                    ->options(Ingredient::UNITS)
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->live(),
                TextInput::make('low_stock_threshold')
                    ->label('Peringatan Stok Rendah')
                    ->type('text')
                    ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2, ',', '.') : '')
                    ->stripCharacters('.')
                    ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                    ->suffix(fn ($get) => $get('unit') ? ' '.$get('unit') : ''),
                Select::make('batch_mode')
                    ->label('Prioritas Batch')
                    ->options(Ingredient::batchModes())
                    ->default(BatchMode::Fefo->value)
                    ->required()
                    ->native(false)
                    ->helperText(new HtmlString("FEFO: Batch stok dengan kedaluwarsa terdekat dipakai lebih dulu<br>FIFO: Batch stok dengan waktu diterima paling awal dipakai lebih dulu")),
                Repeater::make('batches')
                    ->relationship('batches')
                    ->label('Stok Awal (Batch)')
                    ->addActionLabel('+ Tambah Batch')
                    ->hiddenOn('edit')
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        TextInput::make('quantity')
                            ->label('Jumlah')
                            ->required()
                            ->minValue(0)
                            ->step(0.1)
                            ->type('text')
                            ->mask(\App\Filament\Forms\Components\NumericInput::mask(3))
                            ->stripCharacters('.')
                            ->maxLength(\App\Filament\Forms\Components\NumericInput::maxLength(6, 3))
                            ->mutateStateForValidationUsing(\App\Filament\Forms\Components\NumericInput::validationNormalizer())
                            ->dehydrateStateUsing(fn ($state) => \App\Filament\Forms\Components\NumericInput::normalizeState($state))
                            ->suffix(fn ($get) => $get('../../unit') ? ' '.$get('../../unit') : ''),
                        DatePicker::make('expiry_date')
                            ->label('Tanggal Kadaluarsa')
                            ->nullable()
                            ->native(false)
                            ->required(fn ($get) => $get('../../batch_mode') === BatchMode::Fefo->value)
                            ->helperText(fn ($get) => $get('../../batch_mode') === BatchMode::Fefo->value
                                ? 'Wajib diisi untuk mode FEFO'
                                : null),
                        DateTimePicker::make('received_at')
                            ->label('Diterima Tanggal')
                            ->nullable()
                            ->default(now())
                            ->native(false),
                        TextInput::make('cost_per_unit')
                            ->label('Harga per Unit')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->type('text')
                            ->mask(\App\Filament\Forms\Components\NumericInput::mask(0))
                            ->stripCharacters('.')
                            ->maxLength(\App\Filament\Forms\Components\NumericInput::maxLength(6, 0))
                            ->prefix(fn ($get) => $get('../../unit') ? 'Rp/'.$get('../../unit') : 'Rp'),
                    ])
                    ->defaultItems(0)
                    ->collapsible(),
            ]);
    }
}
