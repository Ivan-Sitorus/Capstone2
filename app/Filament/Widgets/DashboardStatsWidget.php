<?php

namespace App\Filament\Widgets;

use App\Models\IngredientBatch;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class DashboardStatsWidget extends StatsOverviewWidget
{
    protected int | array | null $columns = 4;

    public ?string $from = null;
    public ?string $until = null;

    private function rangeFrom(): string
    {
        return $this->from ? Carbon::parse($this->from)->toDateString() : now()->subDays(6)->toDateString();
    }

    private function rangeUntil(): string
    {
        return $this->until ? Carbon::parse($this->until)->toDateString() : now()->toDateString();
    }

    #[On('dashboard-filters-changed')]
    public function applyRange(string $from, string $until): void
    {
        $this->from = $from;
        $this->until = $until;
    }

    protected function getStats(): array
    {
        $today = today();

        // Penjualan & Transaksi use filter range
        $fromDate = $this->rangeFrom();
        $untilDate = Carbon::parse($this->rangeUntil())->endOfDay();
        $totalRange = (float) Order::whereBetween('created_at', [$fromDate, $untilDate])->sum('total_amount');
        $countRange = Order::whereBetween('created_at', [$fromDate, $untilDate])->count();

        $stokMenipis = IngredientBatch::query()
            ->whereNotNull('initial_quantity')
            ->where('quantity', '>', 0)
            ->whereColumn('quantity', '<', DB::raw('initial_quantity * 0.2'))
            ->distinct('ingredient_id')
            ->count('ingredient_id');
        $stokExpired = IngredientBatch::query()
            ->where('quantity', '>', 0)
            ->whereDate('expiry_date', '<', today()->toDateString())
            ->distinct('ingredient_id')
            ->count('ingredient_id');

        $desc = 'Periode ' . Carbon::parse($this->rangeFrom())->format('d M Y')
              . ' – ' . Carbon::parse($this->rangeUntil())->format('d M Y');

        return [
            Stat::make('Penjualan', 'Rp ' . number_format($totalRange, 0, ',', '.'))
                ->description($desc)
                ->color('success')
                ->icon('heroicon-o-banknotes'),
            Stat::make('Transaksi', $countRange)
                ->description($desc)
                ->color('info')
                ->icon('heroicon-o-shopping-cart'),
            Stat::make('Stok Menipis', $stokMenipis)
                ->description('Bahan baku dengan stok < 20%')
                ->color($stokMenipis > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
            Stat::make('Stok Kedaluwarsa', $stokExpired)
                ->description('Bahan baku dengan batch sudah kedaluwarsa')
                ->color($stokExpired > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-calendar-days'),
        ];
    }
}
