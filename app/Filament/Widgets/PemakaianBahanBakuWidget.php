<?php

namespace App\Filament\Widgets;

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

    private function topIngredient(): ?string
    {
        return DB::table('stock_movements as m')
            ->join('ingredients as i', 'i.id', '=', 'm.ingredient_id')
            ->whereNull('i.deleted_at')
            ->where('m.movement_type', 'sale')
            ->whereDate('m.created_at', '>=', $this->rangeFrom())
            ->whereDate('m.created_at', '<=', $this->rangeUntil())
            ->selectRaw('i.name as ingredient_name, SUM(-m.quantity_change) as total')
            ->groupBy('i.id', 'i.name')
            ->orderByDesc('total')
            ->value('ingredient_name');
    }

    private function selectedIngredient(): ?string
    {
        if ($this->filter === 'top') {
            return $this->topIngredient();
        }
        return $this->filter;
    }

    #[On('dashboard-filters-changed')]
    public function applyRange(string $from, string $until): void
    {
        $this->from = $from;
        $this->until = $until;
        $this->filter = $this->topIngredient();
        $this->cachedData = null;
    }

    public function mount(): void
    {
        parent::mount();
        $this->filter ??= $this->topIngredient();
    }

    protected function getFilters(): ?array
    {
        $top = $this->topIngredient();
        $options = $top ? ['top' => "Terpopuler: {$top}"] : [];

        $allNames = Ingredient::orderBy('name')->pluck('name', 'name');
        foreach ($allNames as $name) {
            $options[$name] = $name;
        }

        return $options ?: null;
    }

    protected function getOptions(): array | RawJs | null
    {
        $ingredientName = $this->selectedIngredient();
        $unit = $ingredientName
            ? Ingredient::where('name', $ingredientName)->value('unit')
            : null;

        // Eloquent menerapkan cast enum pada value() — ambil ->value bila BackedEnum
        $unitValue = $unit instanceof \BackedEnum ? $unit->value : $unit;
        $unitLabel = $unitValue ? " {$unitValue}" : '';

        // RawJs wajib sebagai return penuh (bukan bersarang di array) agar
        // @js() merender callback sebagai function, bukan JSON string/object.
        return RawJs::make(<<<JS
            {
                scales: {
                    y: {
                        ticks: {
                            callback: (value) => value + '{$unitLabel}',
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

        return [
            'datasets' => [
                [
                    'label' => $fromDate->translatedFormat('d M Y') . ' – ' . $untilDate->translatedFormat('d M Y'),
                    'data' => $current,
                    'borderColor' => '#28A745',
                    'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
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
            ->where('m.movement_type', 'sale')
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
