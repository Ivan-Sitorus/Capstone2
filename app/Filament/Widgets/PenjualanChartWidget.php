<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\BarChartWidget;
use Illuminate\Support\Carbon;

class PenjualanChartWidget extends BarChartWidget
{
    public function getHeading(): string
    {
        return 'Penjualan (7 Hari Terakhir)';
    }

    protected function getData(): array
    {
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $days[$date] = 0;
        }

        $rows = Order::query()
            ->selectRaw("to_char(created_at, 'YYYY-MM-DD') as day, sum(total_amount) as total")
            ->whereDate('created_at', '>=', now()->subDays(6)->toDateString())
            ->where('status', '!=', 'dibatalkan')
            ->groupBy('day')
            ->get();

        foreach ($rows as $row) {
            if (array_key_exists($row->day, $days)) {
                $days[$row->day] = (float) $row->total;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Penjualan (Rp)',
                    'data' => array_values($days),
                    'backgroundColor' => '#3B6FD4',
                ],
            ],
            'labels' => array_map(
                fn ($date) => Carbon::parse($date)->format('d M'),
                array_keys($days)
            ),
        ];
    }
}
