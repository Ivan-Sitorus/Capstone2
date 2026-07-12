<?php

namespace App\Filament\Resources\DailyIngredientUsageResource\Tables;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DailyIngredientUsageTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('usage_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('ingredient_name')
                    ->label('Bahan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('Satuan')
                    ->badge()
                    ->sortable(),
                TextColumn::make('jumlah_digunakan')
                    ->label('Jumlah Digunakan')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dicatat')
                    ->dateTime('d M Y, H:i:s')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                Filter::make('usage_date_range')
                    ->label('Rentang Tanggal')
                    ->schema([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('usage_date', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('usage_date', '<=', $date));
                    }),
                SelectFilter::make('ingredient_id')
                    ->label('Bahan')
                    ->relationship('ingredient', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([])
            ->toolbarActions([])
            ->defaultSort('usage_date', 'desc');
    }
}
