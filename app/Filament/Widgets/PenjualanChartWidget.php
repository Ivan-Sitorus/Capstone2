<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;

class PenjualanChartWidget extends LineChartWidget
{
    protected int | string | array $columnSpan = 2;

    public ?string $from = null;
    public ?string $until = null;

    private function rangeFrom(): string
    {
        return $this->from ?? now()->subDays(6)->toDateString();
    }

    private function rangeUntil(): string
    {
        return $this->until ?? now()->toDateString();
    }

    #[On('dashboard-filters-changed')]
    public function applyRange(string $from, string $until): void
    {
        $this->from = $from;
        $this->until = $until;
        $this->cachedData = null;
    }

    public function getHeading(): string
    {
        $from = Carbon::parse($this->rangeFrom())->format('d M Y');
        $until = Carbon::parse($this->rangeUntil())->format('d M Y');
        return "Penjualan ({$from} – {$until})";
    }

    protected function getData(): array
    {
        $fromDate = Carbon::parse($this->rangeFrom());
        $untilDate = Carbon::parse($this->rangeUntil());
        $rangeDays = $fromDate->diffInDays($untilDate) + 1;

        $labels = [];
        $days = [];
        for ($i = 0; $i < $rangeDays; $i++) {
            $date = $fromDate->copy()->addDays($i)->toDateString();
            $labels[] = Carbon::parse($date)->format('d M');
            $days[$date] = 0;
        }

        $current = $this->dailySales($fromDate->toDateString(), $untilDate->toDateString(), $days);
        $previous = $this->dailySales(
            $fromDate->copy()->subDays($rangeDays)->toDateString(),
            $fromDate->copy()->subDay()->toDateString(),
            $days
        );

        $previousLabel = $this->formatDatePeriod(
            $fromDate->copy()->subDays($rangeDays)->toDateString(),
            $fromDate->copy()->subDay()->toDateString()
        );

        return [
            'datasets' => [
                [
                    'label' => $fromDate->format('d M Y') . ' – ' . $untilDate->format('d M Y'),
                    'data' => array_values($current),
                    'borderColor' => '#3B6FD4',
                    'backgroundColor' => 'rgba(59, 111, 212, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => $previousLabel,
                    'data' => array_values($previous),
                    'borderColor' => '#E8692A',
                    'backgroundColor' => 'rgba(232, 105, 42, 0.1)',
                    'fill' => true,
                    'borderDash' => [4, 4],
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

    private function formatDatePeriod(string $from, string $to): string
    {
        return Carbon::parse($from)->format('d M Y') . ' – ' . Carbon::parse($to)->format('d M Y');
    }
}
