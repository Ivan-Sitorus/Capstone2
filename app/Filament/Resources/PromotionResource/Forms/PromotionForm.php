<?php

namespace App\Filament\Resources\PromotionResource\Forms;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Promotion;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PromotionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Promosi')
                ->required()
                ->maxLength(255),
            Select::make('type')
                ->label('Tipe Promosi')
                ->options([
                    Promotion::TYPE_PERCENTAGE => 'Diskon Persen',
                    Promotion::TYPE_FIXED_AMOUNT => 'Diskon Nominal',
                    Promotion::TYPE_BUY_X_GET_Y => 'Beli X Dapat Y',
                    Promotion::TYPE_BUNDLE => 'Bundle',
                ])
                ->required()
                ->default(Promotion::TYPE_PERCENTAGE)
                ->native(false),
            TextInput::make('discount_value')
                ->label('Nilai Diskon')
                ->required()
                ->type('text')
                ->prefix('Rp / %')
                ->stripCharacters('.')
                ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 0, ',', '.') : ''),
            TextInput::make('min_purchase')
                ->label('Min. Pembelian')
                ->nullable()
                ->type('text')
                ->prefix('Rp')
                ->stripCharacters('.')
                ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 0, ',', '.') : ''),
            DatePicker::make('start_date')
                ->label('Tanggal Mulai')
                ->required()
                ->default(now())
                ->native(false),
            DatePicker::make('end_date')
                ->label('Tanggal Berakhir')
                ->required()
                ->native(false),
            Select::make('status')
                ->label('Status')
                ->options([
                    Promotion::STATUS_SCHEDULED => 'Terjadwal',
                    Promotion::STATUS_ACTIVE => 'Aktif',
                    Promotion::STATUS_INACTIVE => 'Tidak Aktif',
                    Promotion::STATUS_EXPIRED => 'Kadaluwarsa',
                ])
                ->required()
                ->default(Promotion::STATUS_SCHEDULED)
                ->native(false),
            TextInput::make('usage_limit')
                ->label('Batas Pemakaian')
                ->nullable()
                ->type('text')
                ->stripCharacters('.')
                ->dehydrateStateUsing(fn ($state) => is_string($state) ? (float) str_replace(',', '.', $state) : $state)
                ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 0, ',', '.') : ''),
            TextInput::make('usage_count')
                ->label('Jumlah Dipakai')
                ->default(0)
                ->disabled()
                ->dehydrated(false)
                ->type('text')
                ->stripCharacters('.')
                ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 0, ',', '.') : ''),
            Textarea::make('description')
                ->label('Deskripsi')
                ->rows(3)
                ->columnSpanFull(),
            Repeater::make('rules')
                ->relationship('rules')
                ->label('Aturan Berlaku')
                ->schema([
                    Select::make('applicable_type')
                        ->label('Tipe Aturan')
                        ->options([
                            'menu' => 'Menu',
                            'category' => 'Kategori',
                        ])
                        ->required()
                        ->native(false)
                        ->live(),
                    Select::make('applicable_id')
                        ->label('Target')
                        ->options(function (callable $get) {
                            $type = $get('applicable_type');

                            if ($type === 'category') {
                                return Category::query()->orderBy('name')->pluck('name', 'id')->all();
                            }

                            return Menu::query()->orderBy('name')->pluck('name', 'id')->all();
                        })
                        ->required()
                        ->searchable()
                        ->native(false),
                ])
                ->columns(2)
                ->defaultItems(0)
                ->addActionLabel('+ Tambah Aturan')
                ->columnSpanFull(),
        ])->columns(2);
    }
}
