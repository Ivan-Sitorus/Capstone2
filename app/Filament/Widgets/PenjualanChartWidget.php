<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Carbon;

class PenjualanChartWidget extends LineChartWidget
{
    protected int | string | array $columnSpan = 2;

    public function getHeading(): string
    {
        return 'Penjualan (7 Hari Terakhir)';
    }

    protected function getData(): array
    {
        $labels = [];
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $labels[] = Carbon::parse($date)->format('d M');
            $days[$date] = 0;
        }

        $current = $this->dailySales(now()->subDays(6)->toDateString(), now()->toDateString(), $days);
        $previous = $this->dailySales(now()->subDays(13)->toDateString(), now()->subDays(7)->toDateString(), $days);

        return [
            'datasets' => [
                [
                    'label' => 'Minggu Ini',
                    'data' => array_values($current),
                    'borderColor' => '#3B6FD4',
                    'backgroundColor' => 'rgba(59, 111, 212, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => '7 Hari Sebelumnya',
                    'data' => array_values($previous),
                    'borderColor' => '#E8692A',
                    'backgroundColor' => 'rgba(232, 105, 42, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    private function dailySales(string $from, string $to, array $days): array
    {
        $result = $days;

        Order::query()
            ->selectRaw("to_char(created_at, 'YYYY-MM-DD') as day, sum(total_amount) as total")
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->where('status', '!=', 'dibatalkan')
            ->groupBy('day')
            ->get()
            ->each(function ($row) use (&$result) {
                if (array_key_exists($row->day, $result)) {
                    $result[$row->day] = (float) $row->total;
                }
            });

        return $result;
    }
}
