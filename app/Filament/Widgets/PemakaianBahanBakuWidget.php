<?php

namespace App\Filament\Widgets;

use App\Models\DailyIngredientUsage;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Carbon;

class PemakaianBahanBakuWidget extends LineChartWidget
{
    protected int | string | array $columnSpan = 2;

    public function getHeading(): string
    {
        return 'Pemakaian Bahan Baku (7 Hari Terakhir)';
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

        $current = $this->dailyUsage(now()->subDays(6)->toDateString(), now()->toDateString(), $days);
        $previous = $this->dailyUsage(now()->subDays(13)->toDateString(), now()->subDays(7)->toDateString(), $days);

        return [
            'datasets' => [
                [
                    'label' => 'Minggu Ini',
                    'data' => array_values($current),
                    'borderColor' => '#28A745',
                    'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
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

    private function dailyUsage(string $from, string $to, array $days): array
    {
        $result = $days;

        DailyIngredientUsage::query()
            ->selectRaw("usage_date::date as day, sum(jumlah_digunakan) as total")
            ->whereDate('usage_date', '>=', $from)
            ->whereDate('usage_date', '<=', $to)
            ->groupBy('day')
            ->get()
            ->each(function ($row) use (&$result) {
                $day = $row->day instanceof \Carbon\CarbonInterface ? $row->day->toDateString() : substr((string) $row->day, 0, 10);
                if (array_key_exists($day, $result)) {
                    $result[$day] = (float) $row->total;
                }
            });

        return $result;
    }
}
