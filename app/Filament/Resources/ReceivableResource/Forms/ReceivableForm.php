<?php

namespace App\Filament\Resources\ReceivableResource\Forms;

use App\Models\Menu;
use App\Models\Receivable;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
                        ->options(fn () => Menu::where('is_available', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->native(false),
                    TextInput::make('quantity')
                        ->label('Jumlah')
                        ->numeric()
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
                ->required()
                ->maxLength(255),
            TextInput::make('amount')
                ->label('Jumlah Total')
                ->required()
                ->type('text')
                ->prefix('Rp')
                ->disabled()
                ->dehydrated(true)
                ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2, ',', '.') : ''),
            DatePicker::make('invoice_date')
                ->label('Tanggal Invoice')
                ->required()
                ->default(now())
                ->native(false),
            DatePicker::make('due_date')
                ->label('Jatuh Tempo')
                ->required()
                ->default(now()->addDays(30))
                ->native(false),
            Select::make('status')
                ->label('Status')
                ->options([
                    Receivable::STATUS_PENDING => 'Pending',
                    Receivable::STATUS_PARTIAL => 'Cicilan',
                    Receivable::STATUS_PAID => 'Lunas',
                    Receivable::STATUS_OVERDUE => 'Jatuh Tempo',
                ])
                ->required()
                ->default(Receivable::STATUS_PENDING)
                ->native(false)
                ->live()
                ->disabled(fn ($get) => (int) $get('paid_amount') > 0),
            TextInput::make('paid_amount')
                ->label('Jumlah Dibayar')
                ->required()
                ->type('text')
                ->prefix('Rp')
                ->stripCharacters('.')
                ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2, ',', '.') : '')
                ->live()
                ->afterStateUpdated(function ($state, Set $set) {
                    if ((int) $state > 0) {
                        $set('status', 'partial');
                    }
                }),
            Textarea::make('notes')
                ->label('Catatan')
                ->rows(3)
                ->maxLength(500)
                ->columnSpanFull(),
        ])->columns(2);
    }
}
