<?php

namespace App\Filament\Widgets;

use App\Enums\MovementType;
use App\Filament\Support\ChartPalette;
use App\Models\Ingredient;
use Filament\Support\RawJs;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class PemakaianBahanBakuWidget extends LineChartWidget
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

    private function defaultIngredient(): ?string
    {
        return Ingredient::query()->orderBy('name')->value('name');
    }

    private function selectedIngredient(): ?string
    {
        return $this->filter ?: $this->defaultIngredient();
    }

    private function selectedUnit(): ?string
    {
        $ingredientName = $this->selectedIngredient();

        if (! $ingredientName) {
            return null;
        }

        $unit = Ingredient::query()->where('name', $ingredientName)->value('unit');

        // Eloquent menerapkan cast enum pada value() — ambil ->value bila BackedEnum.
        return $unit instanceof \BackedEnum ? $unit->value : $unit;
    }

    #[On('dashboard-filters-changed')]
    public function applyRange(string $from, string $until): void
    {
        $this->from = $from;
        $this->until = $until;

        if (blank($this->filter)) {
            $this->filter = $this->defaultIngredient();
        }

        $this->cachedData = null;
    }

    public function mount(): void
    {
        parent::mount();
        $this->filter ??= $this->defaultIngredient();
    }

    protected function getFilters(): ?array
    {
        $options = Ingredient::query()->orderBy('name')->pluck('name', 'name')->all();

        return $options ?: null;
    }

    protected function getOptions(): array | RawJs | null
    {
        $nf = ChartPalette::idNumberFormat();

        // RawJs wajib sebagai return penuh (bukan bersarang di array) agar
        // @js() merender callback sebagai function, bukan JSON string/object.
        return RawJs::make(<<<JS
            {
                interaction: { mode: 'index', intersect: false },
                hover: { mode: 'index', intersect: false },
                scales: {
                    y: {
                        ticks: {
                            callback: function (value) {
                                const datasets = this.chart && this.chart.data ? this.chart.data.datasets : [];
                                const unit = (datasets[0] && datasets[0].unit) || '';
                                const formatted = {$nf}.format(value);
                                return unit ? formatted + ' ' + unit : formatted;
                            },
                        },
                    },
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const unit = (ctx.dataset && ctx.dataset.unit) || '';
                                const formatted = {$nf}.format(ctx.parsed.y);
                                return ctx.dataset.label + ': ' + formatted + (unit ? ' ' + unit : '');
                            },
                        },
                    },
                },
            }
        JS);
    }

    public function getHeading(): string
    {
        $from = Carbon::parse($this->rangeFrom())->translatedFormat('d M Y');
        $until = Carbon::parse($this->rangeUntil())->translatedFormat('d M Y');
        $ingredient = $this->selectedIngredient();
        $prefix = $ingredient ? "Pemakaian {$ingredient}" : 'Pemakaian Bahan Baku';
        return "{$prefix} ({$from} – {$until})";
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

        $current = $this->dailyUsage($fromDate->toDateString(), $untilDate->toDateString());

        $previousFrom = $fromDate->copy()->subDays($rangeDays);
        $previousUntil = $fromDate->copy()->subDay();
        $previous = $this->dailyUsage($previousFrom->toDateString(), $previousUntil->toDateString());

        // Satuan dibawa di dataset (bukan di-bake ke options) agar label sumbu Y ikut
        // berubah saat filter bahan diganti. `wire:ignore` mencegah options di-render ulang,
        // sedangkan data ter-update lewat event `updateChartData`.
        $unit = $this->selectedUnit();

        return [
            'datasets' => [
                [
                    'label' => $fromDate->translatedFormat('d M Y') . ' – ' . $untilDate->translatedFormat('d M Y'),
                    'data' => $current,
                    'borderColor' => '#28A745',
                    'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
                    'fill' => true,
                    'unit' => $unit,
                ],
                [
                    'label' => $previousFrom->translatedFormat('d M Y') . ' – ' . $previousUntil->translatedFormat('d M Y'),
                    'data' => $previous,
                    'borderColor' => '#E8692A',
                    'backgroundColor' => 'rgba(232, 105, 42, 0.1)',
                    'fill' => true,
                    'borderDash' => [4, 4],
                    'unit' => $unit,
                ],
            ],
            'labels' => $labels,
        ];
    }

    private function dailyUsage(string $from, string $to): array
    {
        $days = [];
        $cursor = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay();
        while ($cursor->lte($end)) {
            $days[$cursor->toDateString()] = 0.0;
            $cursor->addDay();
        }

        $ingredient = $this->selectedIngredient();

        $query = DB::table('stock_movements as m')
            ->join('ingredients as i', 'i.id', '=', 'm.ingredient_id')
            ->whereNull('i.deleted_at')
            ->where('m.movement_type', MovementType::Sale->value)
            ->whereDate('m.created_at', '>=', $from)
            ->whereDate('m.created_at', '<=', $to)
            ->selectRaw('m.created_at::date as day, SUM(-m.quantity_change) as total');

        if ($ingredient) {
            $query->where('i.name', $ingredient);
        }

        $query->groupByRaw('m.created_at::date')
            ->get()
            ->each(function ($row) use (&$days) {
                $day = $row->day instanceof \Carbon\CarbonInterface ? $row->day->toDateString() : substr((string) $row->day, 0, 10);
                if (array_key_exists($day, $days)) {
                    $days[$day] = (float) $row->total;
                }
            });

        return array_values($days);
    }
}
