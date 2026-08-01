<?php

namespace App\Filament\Widgets;

use App\Models\IngredientBatch;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class StokMenipisWidget extends TableWidget
{
    protected int | string | array $columnSpan = '1/2';

    protected function getTableHeading(): string
    {
        return 'Stok Menipis';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                IngredientBatch::query()
                    ->with('ingredient')
                    ->whereNotNull('initial_quantity')
                    ->where('quantity', '>', 0)
                    ->whereColumn('quantity', '<', \Illuminate\Support\Facades\DB::raw('initial_quantity * 0.2'))
                    ->orderByRaw('(quantity / initial_quantity) asc')
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
                TextColumn::make('initial_quantity')
                    ->label('Awal')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, (float) $state != (int) $state ? 2 : 0, ',', '.')),
                TextColumn::make('persentase')
                    ->label('Sisa %')
                    ->state(fn (IngredientBatch $record) => round((float) $record->quantity / (float) $record->initial_quantity * 100, 0) . '%')
                    ->badge()
                    ->color(fn ($state) => (int) $state <= 10 ? 'danger' : 'warning'),
            ])
            ->paginated(false);
    }
}
