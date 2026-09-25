<?php

namespace App\Filament\Widgets;

use App\Filament\Support\ChartPalette;
use App\Models\OrderItem;
use Filament\Support\RawJs;
use Filament\Widgets\BarChartWidget;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;

class TopMenuWidget extends BarChartWidget
{
    protected int | string | array $columnSpan = 2;

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
        $this->cachedData = null;
    }

    public function getHeading(): string
    {
        $from = Carbon::parse($this->rangeFrom())->translatedFormat('d M Y');
        $until = Carbon::parse($this->rangeUntil())->translatedFormat('d M Y');
        return "Top 10 Menu ({$from} – {$until})";
    }

    protected function getData(): array
    {
        $rows = OrderItem::query()
            ->selectRaw('menu_id, SUM(quantity) as total_qty')
            ->whereHas('order', fn ($q) => $q->whereBetween('created_at', [
                $this->rangeFrom(),
                Carbon::parse($this->rangeUntil())->endOfDay(),
            ]))
            ->groupBy('menu_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->with('menu:id,name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Terjual',
                    'data' => $rows->map(fn ($r) => (int) $r->total_qty)->values()->all(),
                    'backgroundColor' => ChartPalette::colors($rows->count()),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $rows->map(fn ($r) => $r->menu?->name ?? 'Tanpa Nama')->values()->all(),
        ];
    }

    protected function getOptions(): array | RawJs | null
    {
        $nf = ChartPalette::idNumberFormat();

        return RawJs::make(<<<JS
            {
                indexAxis: 'y',
                interaction: { mode: 'index', intersect: false },
                hover: { mode: 'index', intersect: false },
                scales: {
                    x: {
                        ticks: {
                            callback: (value) => {$nf}.format(value),
                        },
                    },
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ctx.dataset.label + ': ' + {$nf}.format(ctx.parsed.x),
                        },
                    },
                },
            }
        JS);
    }
}
