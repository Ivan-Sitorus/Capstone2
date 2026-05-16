<?php

namespace App\Filament\Widgets;

use App\Models\Ingredient;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockTable extends BaseWidget
{
    protected static ?int $sort = 6;

    protected ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function getTableHeading(): string
    {
        return 'Stok Menipis';
    }

    public function getTableDescription(): string
    {
        return 'Bahan baku yang perlu di-restock';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Ingredient::query()
                    ->active()
                    ->whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM ingredient_batches WHERE ingredient_id = ingredients.id) <= low_stock_threshold')
                    ->orderByRaw('(SELECT COALESCE(SUM(quantity), 0) FROM ingredient_batches WHERE ingredient_id = ingredients.id) ASC')
            )
            ->heading($this->getTableHeading())
            ->description($this->getTableDescription())
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Bahan')
                    ->sortable(),
                TextColumn::make('current_stock')
                    ->label('Stok Saat Ini')
                    ->getStateUsing(fn (Ingredient $record): string => number_format($record->getTotalStock(), 0, ',', '.')),
                TextColumn::make('low_stock_threshold')
                    ->label('Low Stock Threshold')
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('Unit')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color('danger')
                    ->getStateUsing(fn (): string => 'Segera Restock'),
            ]);
    }
}
