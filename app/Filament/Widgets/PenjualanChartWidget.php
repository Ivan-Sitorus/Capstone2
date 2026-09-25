<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
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
        $from = Carbon::parse($this->rangeFrom())->translatedFormat('d M Y');
        $until = Carbon::parse($this->rangeUntil())->translatedFormat('d M Y');
        return "Penjualan ({$from} – {$until})";
    }

    protected function getData(): array
    {
        $fromDate = Carbon::parse($this->rangeFrom());
        $untilDate = Carbon::parse($this->rangeUntil());
        $rangeDays = $fromDate->diffInDays($untilDate) + 1;

        $labels = [];
        for ($i = 0; $i < $rangeDays; $i++) {
            $labels[] = $fromDate->copy()->addDays($i)->translatedFormat('d M');
        }

        $current = $this->dailyTotals($fromDate->toDateString(), $untilDate->toDateString());

        $previousFrom = $fromDate->copy()->subDays($rangeDays);
        $previousUntil = $fromDate->copy()->subDay();
        $previous = $this->dailyTotals($previousFrom->toDateString(), $previousUntil->toDateString());

        return [
            'datasets' => [
                [
                    'label' => $fromDate->translatedFormat('d M Y') . ' – ' . $untilDate->translatedFormat('d M Y'),
                    'data' => $current,
                    'borderColor' => '#3B6FD4',
                    'backgroundColor' => 'rgba(59, 111, 212, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => $previousFrom->translatedFormat('d M Y') . ' – ' . $previousUntil->translatedFormat('d M Y'),
                    'data' => $previous,
                    'borderColor' => '#E8692A',
                    'backgroundColor' => 'rgba(232, 105, 42, 0.1)',
                    'fill' => true,
                    'borderDash' => [4, 4],
                ],
            ],
            'labels' => $labels,
        ];
    }

    private function dailyTotals(string $from, string $to): array
    {
        $days = [];
        $cursor = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay();
        while ($cursor->lte($end)) {
            $days[$cursor->toDateString()] = 0.0;
            $cursor->addDay();
        }

        Order::query()
            ->selectRaw("to_char(created_at, 'YYYY-MM-DD') as day, sum(total_amount) as total")
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->where('status', '!=', OrderStatus::Cancelled->value)
            ->groupBy('day')
            ->get()
            ->each(function ($row) use (&$days) {
                if (array_key_exists($row->day, $days)) {
                    $days[$row->day] = (float) $row->total;
                }
            });

        return array_values($days);
    }
}
