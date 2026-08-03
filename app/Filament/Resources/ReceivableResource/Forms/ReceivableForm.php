<?php

namespace App\Filament\Resources\ReceivableResource\Forms;

use App\Filament\Forms\Components\NumericInput;
use App\Models\Menu;
use App\Models\Receivable;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ReceivableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Repeater::make('items')
                ->label('Item Pesanan')
                ->schema([
                    Select::make('menu_id')
                        ->label('Menu')
                        ->options(fn () => Menu::where('status', 'active')->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->native(false),
                    NumericInput::apply(TextInput::make('quantity'), maxDigits: 5)
                        ->label('Jumlah')
                        ->default(1)
                        ->minValue(1)
                        ->required(),
                ])
                ->columns(2)
                ->reorderable(false)
                ->addActionLabel('+ Tambah Item')
                ->columnSpanFull()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->live()
                ->afterStateUpdated(function ($state, Set $set) {
                    if (! is_array($state)) {
                        return;
                    }
                    $menuIds = collect($state)->pluck('menu_id')->filter()->unique();
                    $menus = Menu::whereIn('id', $menuIds)->pluck('price', 'id');
                    $total = collect($state)->sum(fn ($item) => ($menus[$item['menu_id']] ?? 0) * (int) ($item['quantity'] ?? 0));
                    $set('amount', $total);
                }),
            TextInput::make('customer_name')
                ->label('Nama Pelanggan')
                ->maxLength(255),
            TextInput::make('amount')
                ->label('Jumlah Total')
                ->required()
                ->type('text')
                ->prefix('Rp')
                ->disabled()
                ->dehydrated(true)
                ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2, ',', '.') : ''),
            NumericInput::apply(TextInput::make('paid_amount'), maxDigits: 9)
                ->label('Jumlah Dibayar')
                ->required()
                ->prefix('Rp')
                ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2, ',', '.') : '')
                ->live()
                ->afterStateUpdated(function ($state, Set $set) {
                    if ((int) $state > 0) {
                        $set('status', 'partial');
                    }
                }),
        ])->columns(2);
    }
}
