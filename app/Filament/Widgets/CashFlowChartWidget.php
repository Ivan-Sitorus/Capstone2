<?php

namespace App\Filament\Widgets;

use App\Models\IngredientBatch;
use App\Models\Order;
use App\Models\UnexpectedTransaction;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class CashFlowChartWidget extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    public static function isLazy(): bool
    {
        return false;
    }

    public function getHeading(): string
    {
        return 'Analisis Arus Kas';
    }

    public function getDescription(): ?string
    {
        return 'Pemasukan dari transaksi vs Pengeluaran';
    }

    private function incomeQuery(): Builder
    {
        return Order::where('is_paid', true);
    }

    private function slotIncome($start, $end): float
    {
        $orders = (float) $this->incomeQuery()
            ->whereBetween('created_at', [$start, $end])->sum('total_amount');
        $unexpected = (float) UnexpectedTransaction::where('jenis', 'pemasukan')
            ->whereBetween('created_at', [$start, $end])->sum('nominal');

        return $orders + $unexpected;
    }

    private function slotExpense($start, $end): float
    {
        $batches = (float) IngredientBatch::whereBetween('received_at', [$start, $end])
            ->selectRaw('SUM(quantity * cost_per_unit) as total')->value('total') ?? 0;
        $unexpected = (float) UnexpectedTransaction::where('jenis', 'pengeluaran')
            ->whereBetween('created_at', [$start, $end])->sum('nominal');

        return $batches + $unexpected;
    }

    private function buildData(): array
    {
        $cacheKey = 'cashflow_chart_'.md5(json_encode($this->pageFilters));

        return Cache::remember($cacheKey, 300, function () {
            $period = $this->pageFilters['period'] ?? 'today';

            return match ($period) {
                'today' => $this->byHour(),
                'this_week' => $this->byDayRange(
                    Carbon::now()->startOfWeek(Carbon::MONDAY),
                    Carbon::now()->endOfWeek(Carbon::SUNDAY)
                ),
                'this_month' => $this->byEvenDay(),
                default => $this->byEvenDay(),
            };
        });
    }

    private function byDayRange(Carbon $start, Carbon $end): array
    {
        $labels = $incData = $expData = [];
        $cur = $start->copy();

        while ($cur->lte($end)) {
            $dayStart = $cur->copy()->startOfDay();
            $dayEnd = $cur->copy()->endOfDay();
            $labels[] = $cur->isoFormat('D MMM');
            $incData[] = $this->slotIncome($dayStart, $dayEnd);
            $expData[] = $this->slotExpense($dayStart, $dayEnd);
            $cur->addDay();
        }

        return compact('labels', 'incData', 'expData');
    }

    private function byWeekRange(Carbon $start, Carbon $end): array
    {
        $labels = $incData = $expData = [];
        $cur = $start->copy()->startOfWeek();
        $weekNum = 1;

        while ($cur->lte($end)) {
            $weekEnd = $cur->copy()->endOfWeek();
            $slotEnd = $weekEnd->gt($end) ? $end->copy() : $weekEnd;

            $labels[] = 'Minggu '.$weekNum;
            $incData[] = $this->slotIncome($cur->copy()->startOfDay(), $slotEnd->copy()->endOfDay());
            $expData[] = $this->slotExpense($cur->copy()->startOfDay(), $slotEnd->copy()->endOfDay());

            $cur->addWeek();
            $weekNum++;
        }

        return compact('labels', 'incData', 'expData');
    }

    private function byMonthRange(Carbon $start, Carbon $end): array
    {
        $labels = $incData = $expData = [];
        $cur = $start->copy()->startOfMonth();

        while ($cur->lte($end)) {
            $monthEnd = $cur->copy()->endOfMonth();
            $slotEnd = $monthEnd->gt($end) ? $end->copy() : $monthEnd;

            $labels[] = $cur->isoFormat('MMM YYYY');
            $incData[] = $this->slotIncome($cur->copy()->startOfDay(), $slotEnd->copy()->endOfDay());
            $expData[] = $this->slotExpense($cur->copy()->startOfDay(), $slotEnd->copy()->endOfDay());

            $cur->addMonth();
        }

        return compact('labels', 'incData', 'expData');
    }

    /** Hari Ini: per 3 jam (00:00, 03:00, 06:00, …, 21:00) */
    private function byHour(): array
    {
        $labels = $incData = $expData = [];
        $today = Carbon::today();

        foreach ([0, 3, 6, 9, 12, 15, 18, 21] as $h) {
            $start = $today->copy()->setHour($h)->setMinute(0)->setSecond(0);
            $end = $today->copy()->setHour($h + 2)->setMinute(59)->setSecond(59);

            $labels[] = sprintf('%02d:00', $h);
            $incData[] = $this->slotIncome($start, $end);
            $expData[] = $this->slotExpense($start, $end);
        }

        return compact('labels', 'incData', 'expData');
    }

    /** Bulan Ini: tanggal genap saja (2, 4, 6 … akhir bulan) */
    private function byEvenDay(): array
    {
        $labels = $incData = $expData = [];
        $s = Carbon::now()->startOfMonth();
        $e = Carbon::now()->endOfMonth();
        $cur = $s->copy();

        while ($cur->lte($e)) {
            if ($cur->day % 2 === 0) {
                $dayStart = $cur->copy()->startOfDay();
                $dayEnd = $cur->copy()->endOfDay();
                $labels[] = $cur->format('d/m');
                $incData[] = $this->slotIncome($dayStart, $dayEnd);
                $expData[] = $this->slotExpense($dayStart, $dayEnd);
            }
            $cur->addDay();
        }

        return compact('labels', 'incData', 'expData');
    }

    protected function getData(): array
    {
        ['labels' => $labels, 'incData' => $incData, 'expData' => $expData] = $this->buildData();

        $shared = [
            'fill' => true,
            'tension' => 0.45,
            'borderWidth' => 2.5,
            'pointRadius' => 4,
            'pointHoverRadius' => 7,
            'pointBorderWidth' => 2,
            'pointBorderColor' => '#ffffff',
            'cubicInterpolationMode' => 'monotone',
        ];

        return [
            'datasets' => [
                array_merge($shared, [
                    'label' => 'Pemasukan',
                    'data' => $incData,
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34,197,94,0.12)',
                    'pointBackgroundColor' => '#22c55e',
                ]),
                array_merge($shared, [
                    'label' => 'Pengeluaran',
                    'data' => $expData,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239,68,68,0.12)',
                    'pointBackgroundColor' => '#ef4444',
                ]),
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
        {
            animation: { duration: 600 },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 24, color: '#94a3b8' }
                },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,0.92)',
                    titleColor: '#e2e8f0',
                    bodyColor: '#94a3b8',
                    borderColor: 'rgba(255,255,255,0.08)',
                    borderWidth: 1,
                    padding: 12,
                    callbacks: {
                        label: (ctx) => {
                            const v = ctx.parsed.y;
                            const fmt = new Intl.NumberFormat('id-ID').format(Math.round(v));
                            return ' ' + ctx.dataset.label + ': Rp ' + fmt + ',-';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(148,163,184,0.07)' },
                    ticks: { color: '#64748b', maxRotation: 0 },
                    border: { display: false }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(148,163,184,0.07)' },
                    ticks: {
                        color: '#64748b',
                        padding: 8,
                        callback: (v) => {
                            if (v >= 1000000) return 'Rp ' + (v/1000000).toFixed(1) + 'jt';
                            if (v >= 1000)    return 'Rp ' + (v/1000).toFixed(0) + 'rb';
                            return v === 0 ? '0' : 'Rp ' + v;
                        }
                    },
                    border: { display: false }
                }
            }
        }
        JS);
    }
}
