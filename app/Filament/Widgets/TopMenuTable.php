<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

class TopMenuTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 1;

    public function getTableHeading(): string
    {
        return 'Menu Terlaris';
    }

    public function table(Table $table): Table
    {
        $period = $this->pageFilters['period'] ?? 'today';

        [$start, $end] = match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'this_week' => [now()->startOfWeek(Carbon::MONDAY), now()->endOfWeek(Carbon::SUNDAY)],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [now()->startOfDay(), now()->endOfDay()],
        };

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
