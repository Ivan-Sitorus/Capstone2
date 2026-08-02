<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
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
        $from = Carbon::parse($this->rangeFrom())->format('d M Y');
        $until = Carbon::parse($this->rangeUntil())->format('d M Y');
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
                    'backgroundColor' => '#6B4FBB',
                ],
            ],
            'labels' => $rows->map(fn ($r) => $r->menu?->name ?? 'Tanpa Nama')->values()->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ];
    }
}
