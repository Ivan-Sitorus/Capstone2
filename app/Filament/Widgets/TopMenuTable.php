<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TopMenuTable extends BaseWidget
{
    public function getTableRecordKey(Model|array $record): string
    {
        if (is_array($record)) {
            return (string) ($record['menu_id'] ?? array_key_first($record));
        }

        return (string) ($record->menu_id ?? spl_object_id($record));
    }

    protected ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 1;

    public function getTableHeading(): string
    {
        return 'Menu Terlaris';
    }

    public function table(Table $table): Table
    {
        [$start, $end] = [now()->startOfMonth(), now()->endOfMonth()];

        return $table
            ->query(
                OrderItem::query()
                    ->selectRaw('menu_id, SUM(quantity) as total_sold, SUM(subtotal) as total_revenue')
                    ->whereHas('order', fn (Builder $q) => $q
                        ->where('is_paid', true)
                        ->whereBetween('created_at', [$start, $end])
                    )
                    ->groupBy('menu_id')
                    ->orderByDesc('total_sold')
                    ->limit(5)
            )
            ->defaultKeySort(false)
            ->heading($this->getTableHeading())
            ->paginated(false)
            ->columns([
                TextColumn::make('menu.name')
                    ->label('Menu')
                    ->sortable(),
                TextColumn::make('total_sold')
                    ->label('Terjual')
                    ->sortable(),
                TextColumn::make('total_revenue')
                    ->label('Pendapatan')
                    ->formatStateUsing(fn (int $state): string => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),
            ]);
    }
}
