<?php

namespace App\Filament\Widgets;

use App\Models\DailyIngredientUsage;
use Filament\Widgets\BarChartWidget;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;

class TopBahanBakuWidget extends BarChartWidget
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
        return "Top 10 Bahan Baku ({$from} – {$until})";
    }

    protected function getData(): array
    {
        $rows = DailyIngredientUsage::query()
            ->selectRaw('ingredient_name, unit, SUM(jumlah_digunakan) as total')
            ->whereBetween('usage_date', [$this->rangeFrom(), $this->rangeUntil()])
            ->groupBy('ingredient_name', 'unit')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Dipakai',
                    'data' => $rows->map(fn ($r) => (float) $r->total)->values()->all(),
                    'backgroundColor' => '#17A2B8',
                ],
            ],
            'labels' => $rows->map(fn ($r) => $r->ingredient_name . ($r->unit?->value ? ' (' . $r->unit->value . ')' : ''))->values()->all(),
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
