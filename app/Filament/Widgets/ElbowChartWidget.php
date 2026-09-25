<?php

namespace App\Filament\Widgets;

use App\Filament\Support\ChartPalette;
use App\Models\DataminingRun;
use Filament\Support\RawJs;
use Filament\Widgets\LineChartWidget;

class ElbowChartWidget extends LineChartWidget
{
    protected int | string | array $columnSpan = 1;

    public ?int $runId = null;

    public function getHeading(): string
    {
        return 'Elbow Method (Inertia per K)';
    }

    protected function getData(): array
    {
        $run = DataminingRun::find($this->runId);
        $elbow = $run?->payload['elbow'] ?? [];

        return [
            'datasets' => [
                [
                    'label' => 'Inertia',
                    'data' => array_map('floatval', $elbow['inertia'] ?? []),
                    'borderColor' => '#6366f1',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'fill' => false,
                ],
            ],
            'labels' => array_map('strval', $elbow['k'] ?? []),
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
}
