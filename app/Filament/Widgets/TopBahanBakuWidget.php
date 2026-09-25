<?php

namespace App\Filament\Widgets;

use App\Enums\MovementType;
use Filament\Widgets\BarChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        $from = Carbon::parse($this->rangeFrom())->translatedFormat('d M Y');
        $until = Carbon::parse($this->rangeUntil())->translatedFormat('d M Y');
        return "Top 10 Bahan Baku — Biaya Pemakaian ({$from} – {$until})";
    }

    protected function getData(): array
    {
        $rows = DB::table('stock_movements as m')
            ->join('ingredients as i', 'i.id', '=', 'm.ingredient_id')
            ->leftJoin(
                DB::raw('(SELECT ingredient_id, CASE WHEN SUM(initial_quantity) > 0 THEN SUM(total_cost) / SUM(initial_quantity) ELSE 0 END AS unit_cost FROM ingredient_batches WHERE deleted_at IS NULL GROUP BY ingredient_id) as b'),
                'b.ingredient_id',
                '=',
                'i.id'
            )
            ->whereNull('i.deleted_at')
            ->where('m.movement_type', MovementType::Sale->value)
            ->whereDate('m.created_at', '>=', $this->rangeFrom())
            ->whereDate('m.created_at', '<=', $this->rangeUntil())
            ->selectRaw('i.name as ingredient_name, i.unit as unit, SUM(-m.quantity_change) * COALESCE(b.unit_cost, 0) as total')
            ->groupBy('i.id', 'i.name', 'i.unit', 'b.unit_cost')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Biaya Pemakaian (Rp)',
                    'data' => $rows->map(fn ($r) => round((float) $r->total, 2))->values()->all(),
                    'backgroundColor' => '#17A2B8',
                ],
            ],
            'labels' => $rows->map(fn ($r) => $r->ingredient_name . ($r->unit ? ' (' . $r->unit . ')' : ''))->values()->all(),
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
