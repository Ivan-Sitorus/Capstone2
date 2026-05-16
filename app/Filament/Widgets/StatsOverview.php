<?php

namespace App\Filament\Widgets;

use App\Models\Menu;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class StatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $period = $this->pageFilters['period'] ?? 'today';

        [$salesStart, $salesEnd, $prevSalesStart, $prevSalesEnd, $salesLabel] = match ($period) {
            'today' => [
                today()->startOfDay(), today()->endOfDay(),
                today()->subDay()->startOfDay(), today()->subDay()->endOfDay(),
                'Penjualan Hari Ini',
            ],
            'this_week' => [
                now()->startOfWeek(Carbon::MONDAY), now()->endOfWeek(Carbon::SUNDAY),
                now()->subWeek()->startOfWeek(Carbon::MONDAY), now()->subWeek()->endOfWeek(Carbon::SUNDAY),
                'Penjualan Minggu Ini',
            ],
            'this_month' => [
                now()->startOfMonth(), now()->endOfMonth(),
                now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth(),
                'Penjualan Bulan Ini',
            ],
            default => [
                today()->startOfDay(), today()->endOfDay(),
                today()->subDay()->startOfDay(), today()->subDay()->endOfDay(),
                'Penjualan Hari Ini',
            ],
        };

        // 1 query: ambil penjualan 7 hari terakhir sekaligus
        $salesByDay = Order::where('is_paid', true)
            ->whereBetween(DB::raw('created_at::date'), [today()->subDays(6)->toDateString(), today()->toDateString()])
            ->selectRaw('created_at::date as day, SUM(total_amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $last7Days[] = (float) ($salesByDay[today()->subDays($i)->toDateString()] ?? 0);
        }

        $salesCurrent = (float) Order::where('is_paid', true)
            ->whereBetween('created_at', [$salesStart, $salesEnd])
            ->sum('total_amount');

        $salesPrev = (float) Order::where('is_paid', true)
            ->whereBetween('created_at', [$prevSalesStart, $prevSalesEnd])
            ->sum('total_amount');

        $salesChange = $salesPrev > 0
            ? round((($salesCurrent - $salesPrev) / $salesPrev) * 100, 1)
            : 0;

        // 1 query: count menu + categories
        $menuStats = Menu::where('is_available', true)
            ->selectRaw('COUNT(*) as total_menu, COUNT(DISTINCT category_id) as total_categories')
            ->first();

        // 1 query: orders bulan ini & bulan lalu sekaligus
        $ordersPerMonth = Order::selectRaw(
            "DATE_TRUNC('month', created_at) as month, COUNT(*) as total"
        )
            ->whereIn(DB::raw("DATE_TRUNC('month', created_at)"), [
                today()->startOfMonth()->toDateString(),
                today()->subMonth()->startOfMonth()->toDateString(),
            ])
            ->groupBy('month')
            ->pluck('total', 'month')
            ->mapWithKeys(fn ($v, $k) => [substr($k, 0, 7) => $v]);

        $ordersThisMonth = $ordersPerMonth[today()->format('Y-m')] ?? 0;
        $ordersLastMonth = $ordersPerMonth[today()->subMonth()->format('Y-m')] ?? 0;
        $ordersChange = $ordersLastMonth > 0
            ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100, 1)
            : 0;

        return [
            Stat::make($salesLabel, 'Rp '.number_format($salesCurrent, 0, ',', '.'))
                ->description(($salesChange >= 0 ? '↑ ' : '↓ ').abs($salesChange).'% dari periode lalu')
                ->descriptionIcon($salesChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($salesChange >= 0 ? 'success' : 'danger')
                ->chart($last7Days),

            Stat::make('Menu Tersedia', $menuStats->total_menu ?? 0)
                ->description(($menuStats->total_categories ?? 0).' kategori aktif')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Pesanan Bulan Ini', $ordersThisMonth)
                ->description(($ordersChange >= 0 ? '↑ ' : '↓ ').abs($ordersChange).'% dari bulan lalu')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),
        ];
    }
}
