<?php

namespace App\Filament\Widgets;

use App\Models\DailyIngredientUsage;
use App\Models\Ingredient;
use Filament\Support\RawJs;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Carbon;
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
        return DailyIngredientUsage::query()
            ->selectRaw('ingredient_name, SUM(jumlah_digunakan) as total')
            ->whereBetween('usage_date', [$this->rangeFrom(), $this->rangeUntil()])
            ->groupBy('ingredient_name')
            ->orderByDesc('total')
            ->first()?->ingredient_name;
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
        $days = [];
        for ($i = 0; $i < $rangeDays; $i++) {
            $date = $fromDate->copy()->addDays($i)->toDateString();
            $labels[] = Carbon::parse($date)->translatedFormat('d M');
            $days[$date] = 0;
        }

        $current = $this->dailyUsage($fromDate->toDateString(), $untilDate->toDateString(), $days);
        $previous = $this->dailyUsage(
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
                    'label' => $fromDate->translatedFormat('d M Y') . ' – ' . $untilDate->translatedFormat('d M Y'),
                    'data' => array_values($current),
                    'borderColor' => '#28A745',
                    'backgroundColor' => 'rgba(40, 167, 69, 0.1)',
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

    private function dailyUsage(string $from, string $to, array $days): array
    {
        $result = $days;
        $ingredient = $this->selectedIngredient();

        $query = DailyIngredientUsage::query()
            ->selectRaw("usage_date::date as day, sum(jumlah_digunakan) as total")
            ->whereDate('usage_date', '>=', $from)
            ->whereDate('usage_date', '<=', $to);

        if ($ingredient) {
            $query->where('ingredient_name', $ingredient);
        }

        $query->groupBy('day')
            ->get()
            ->each(function ($row) use (&$result) {
                $day = $row->day instanceof \Carbon\CarbonInterface ? $row->day->toDateString() : substr((string) $row->day, 0, 10);
                if (array_key_exists($day, $result)) {
                    $result[$day] = (float) $row->total;
                }
            });

        return $result;
    }

    private function formatDatePeriod(string $from, string $to): string
    {
        return Carbon::parse($from)->translatedFormat('d M Y') . ' – ' . Carbon::parse($to)->translatedFormat('d M Y');
    }
}
