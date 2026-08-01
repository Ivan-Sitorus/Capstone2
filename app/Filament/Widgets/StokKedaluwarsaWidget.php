<?php

namespace App\Filament\Widgets;

use App\Models\IngredientBatch;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class StokKedaluwarsaWidget extends TableWidget
{
    protected int | string | array $columnSpan = '1/2';

    protected function getTableHeading(): string
    {
        return 'Segera Kedaluwarsa';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                IngredientBatch::query()
                    ->with('ingredient')
                    ->where('quantity', '>', 0)
                    ->whereDate('expiry_date', '>=', now()->toDateString())
                    ->whereDate('expiry_date', '<=', now()->addDays(7)->toDateString())
                    ->orderBy('expiry_date', 'asc')
            )
            ->columns([
                TextColumn::make('ingredient.name')
                    ->label('Bahan Baku'),
                TextColumn::make('batch_code')
                    ->label('Kode')
                    ->default('-'),
                TextColumn::make('quantity')
                    ->label('Sisa')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.')),
                TextColumn::make('expiry_date')
                    ->label('Kedaluwarsa')
                    ->date('d M Y'),
                TextColumn::make('hari_tersisa')
                    ->label('Hari')
                    ->state(fn (IngredientBatch $record) => now()->diffInDays($record->expiry_date) + 1)
                    ->badge()
                    ->color(fn ($state) => (int) $state <= 1 ? 'danger' : ((int) $state <= 3 ? 'warning' : 'info')),
            ])
            ->paginated(false);
    }
}
