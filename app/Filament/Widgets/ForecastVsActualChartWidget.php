<?php

namespace App\Filament\Widgets;

use App\Filament\Support\ChartPalette;
use App\Models\DataminingRun;
use Filament\Support\RawJs;
use Filament\Widgets\LineChartWidget;

class ForecastVsActualChartWidget extends LineChartWidget
{
    protected int | string | array $columnSpan = 'full';

    public ?int $runId = null;

    public function getHeading(): string
    {
        $name = $this->filter ?? ($this->items()[0] ?? '');

        return 'Prediksi vs Aktual (Test): ' . $name;
    }

    public function mount(): void
    {
        parent::mount();
        $this->filter ??= $this->items()[0] ?? null;
    }

    protected function getFilters(): ?array
    {
        $items = $this->items();

        return empty($items) ? null : collect($items)->mapWithKeys(fn ($n) => [$n => $n])->all();
    }

    protected function getData(): array
    {
        $selected = $this->selected();

        if ($selected === null) {
            return ['datasets' => [], 'labels' => []];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Aktual (Test)',
                    'data' => $selected['actual'],
                    'borderColor' => '#059669',
                    'backgroundColor' => 'rgba(5, 150, 105, 0.1)',
                    'fill' => false,
                ],
                [
                    'label' => 'Prediksi',
                    'data' => $selected['predicted'],
                    'borderColor' => '#E8692A',
                    'backgroundColor' => 'rgba(232, 105, 42, 0.1)',
                    'borderDash' => [4, 4],
                    'fill' => false,
                ],
            ],
            'labels' => $selected['labels'],
        ];
    }

    protected function getOptions(): array | RawJs | null
    {
        $nf = ChartPalette::idNumberFormat();

        return RawJs::make(<<<JS
            {
                interaction: { mode: 'index', intersect: false },
                hover: { mode: 'index', intersect: false },
                scales: {
                    y: {
                        ticks: {
                            callback: (value) => {$nf}.format(value),
                        },
                    },
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ctx.dataset.label + ': ' + {$nf}.format(ctx.parsed.y),
                        },
                    },
                },
            }
        JS);
    }

    private function payload(): array
    {
        return DataminingRun::find($this->runId)?->payload ?? [];
    }

    private function items(): array
    {
        $payload = $this->payload();

        if (! empty($payload['forecast_test'])) {
            return array_map(fn ($r) => $r['nama'], $payload['forecast_test']);
        }

        if (! empty($payload['all_items'])) {
            return array_map(fn ($r) => $r['bahan'], $payload['all_items']);
        }

        return [];
    }

    private function selected(): ?array
    {
        $payload = $this->payload();

        if (! empty($payload['forecast_test'])) {
            $row = collect($payload['forecast_test'])->firstWhere('nama', $this->filter) ?? $payload['forecast_test'][0];

            return ['labels' => $row['ds'], 'actual' => $row['actual'], 'predicted' => $row['predicted']];
        }

        if (! empty($payload['all_items'])) {
            $row = collect($payload['all_items'])->firstWhere('bahan', $this->filter) ?? $payload['all_items'][0];

            return ['labels' => $row['test_ds'], 'actual' => $row['test_y'], 'predicted' => $row['pred_y']];
        }

        return null;
    }
}
