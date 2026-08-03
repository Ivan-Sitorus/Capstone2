<?php

namespace App\Filament\Resources\StockResource\Forms;

use App\Enums\BatchMode;
use App\Filament\Forms\Components\NumericInput;
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
                NumericInput::apply(TextInput::make('low_stock_threshold'), maxDigits: 6, precision: 3)
                    ->label('Peringatan Stok Rendah')
                    ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2, ',', '.') : '')
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
                        NumericInput::apply(TextInput::make('quantity'), maxDigits: 6, precision: 3)
                            ->label('Jumlah')
                            ->required()
                            ->minValue(0)
                            ->step(0.1)
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
                        NumericInput::apply(TextInput::make('cost_per_unit'), maxDigits: 9)
                            ->label('Harga per Unit')
                            ->required()
                            ->minValue(0)
                            ->prefix(fn ($get) => $get('../../unit') ? 'Rp/'.$get('../../unit') : 'Rp'),
                    ])
                    ->defaultItems(0)
                    ->collapsible(),
            ]);
    }
}
