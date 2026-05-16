<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class DailySalesChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 3;

    public function getHeading(): string
    {
        return 'Tren Penjualan';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $period = $this->pageFilters['period'] ?? 'today';

        return match ($period) {
            'today' => $this->byHour(),
            'this_week' => $this->byDayRange(
                Carbon::now()->startOfWeek(Carbon::MONDAY),
                Carbon::now()->endOfWeek(Carbon::SUNDAY),
            ),
            'this_month' => $this->byEvenDay(),
            default => $this->byEvenDay(),
        };
    }

    /** Today: 6 buckets of 4 hours each */
    private function byHour(): array
    {
        $labels = $revenue = [];
        $today = Carbon::today();

        $buckets = [
            ['00:00', '03:59', 0, 3],
            ['04:00', '07:59', 4, 7],
            ['08:00', '11:59', 8, 11],
            ['12:00', '15:59', 12, 15],
            ['16:00', '19:59', 16, 19],
            ['20:00', '23:59', 20, 23],
        ];

        foreach ($buckets as [$label, $_, $startHour, $endHour]) {
            $start = $today->copy()->setHour($startHour)->setMinute(0)->setSecond(0);
            $end = $today->copy()->setHour($endHour)->setMinute(59)->setSecond(59);

            $labels[] = $label;
            $revenue[] = (float) Order::where('is_paid', true)
                ->whereBetween('created_at', [$start, $end])
                ->sum('total_amount');
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Penjualan',
                    'data' => $revenue,
                    'backgroundColor' => '#3B6FD4',
                ],
            ],
        ];
    }

    /** This Week: one bar per day (Mon–Sun) */
    private function byDayRange(Carbon $start, Carbon $end): array
    {
        $labels = $revenue = [];
        $cur = $start->copy();

        while ($cur->lte($end)) {
            $dayStart = $cur->copy()->startOfDay();
            $dayEnd = $cur->copy()->endOfDay();

            $labels[] = $cur->isoFormat('D MMM');
            $revenue[] = (float) Order::where('is_paid', true)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->sum('total_amount');

            $cur->addDay();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Penjualan',
                    'data' => $revenue,
                    'backgroundColor' => '#3B6FD4',
                ],
            ],
        ];
    }

    /** This Month: every other day (~15 bars) */
    private function byEvenDay(): array
    {
        $labels = $revenue = [];
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();
        $cur = $start->copy();

        while ($cur->lte($end)) {
            if ($cur->day % 2 === 0) {
                $dayStart = $cur->copy()->startOfDay();
                $dayEnd = $cur->copy()->endOfDay();

                $labels[] = $cur->format('d/m');
                $revenue[] = (float) Order::where('is_paid', true)
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->sum('total_amount');
            }
            $cur->addDay();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Penjualan',
                    'data' => $revenue,
                    'backgroundColor' => '#3B6FD4',
                ],
            ],
        ];
    }
}
