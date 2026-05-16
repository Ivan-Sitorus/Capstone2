<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class SalesOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected ?string $heading = 'Penjualan Menu';

    protected ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    private function pctChange(float $curr, float $prev): ?float
    {
        return $prev > 0 ? (($curr - $prev) / $prev) * 100 : null;
    }

    private function descriptionText(?float $pct): string
    {
        if ($pct === null) {
            return 'Periode pertama';
        }

        $icon = $pct >= 0 ? '↑ ' : '↓ ';

        return $icon.number_format(abs($pct), 1).'% dari periode lalu';
    }

    private function trendIcon(?float $pct): string
    {
        if ($pct === null) {
            return 'heroicon-m-arrow-trending-up';
        }

        return $pct >= 0
            ? 'heroicon-m-arrow-trending-up'
            : 'heroicon-m-arrow-trending-down';
    }

    private function trendColor(?float $pct): string
    {
        if ($pct === null) {
            return 'success';
        }

        return $pct >= 0 ? 'success' : 'danger';
    }

    private function fmtRupiah(int $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    protected function getStats(): array
    {
        $period = $this->pageFilters['period'] ?? 'today';

        [$start, $end] = match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'this_week' => [now()->startOfWeek(Carbon::MONDAY), now()->endOfWeek(Carbon::SUNDAY)],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [now()->startOfDay(), now()->endOfDay()],
        };

        $days = (int) $start->diffInDays($end) + 1;
        $prevEnd = $start->copy()->subDay()->endOfDay();
        $prevStart = $prevEnd->copy()->subDays($days - 1)->startOfDay();

        $totalRevenue = (int) Order::where('is_paid', true)
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_amount');

        $prevRevenue = (int) Order::where('is_paid', true)
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->sum('total_amount');

        $revenuePct = $this->pctChange($totalRevenue, $prevRevenue);

        $sparkline = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $sparkline[] = (int) Order::where('is_paid', true)
                ->whereDate('created_at', $day->toDateString())
                ->sum('total_amount');
        }

        $totalOrders = Order::where('is_paid', true)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $prevOrders = Order::where('is_paid', true)
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        $ordersPct = $this->pctChange($totalOrders, $prevOrders);

        $avgOrder = $totalOrders > 0 ? (int) round($totalRevenue / $totalOrders) : 0;
        $prevAvgOrder = $prevOrders > 0 ? (int) round($prevRevenue / $prevOrders) : 0;
        $avgPct = $this->pctChange($avgOrder, $prevAvgOrder);

        $activeOrders = Order::whereNotIn('status', ['selesai', 'completed', 'cancelled'])->count();

        return [
            Stat::make('Total Pendapatan', $this->fmtRupiah($totalRevenue))
                ->description($this->descriptionText($revenuePct))
                ->descriptionIcon($this->trendIcon($revenuePct))
                ->color($this->trendColor($revenuePct))
                ->chart($sparkline),

            Stat::make('Total Pesanan', $totalOrders)
                ->description($this->descriptionText($ordersPct))
                ->descriptionIcon($this->trendIcon($ordersPct))
                ->color($this->trendColor($ordersPct)),

            Stat::make('Rata-rata Pesanan', $this->fmtRupiah($avgOrder))
                ->description($this->descriptionText($avgPct))
                ->descriptionIcon($this->trendIcon($avgPct))
                ->color($this->trendColor($avgPct)),

            Stat::make('Pesanan Aktif', $activeOrders)
                ->description('Pesanan yang sedang diproses')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
