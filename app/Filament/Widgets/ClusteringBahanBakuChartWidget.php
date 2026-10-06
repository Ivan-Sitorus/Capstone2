<?php

namespace App\Filament\Widgets;

use App\Filament\Support\ChartPalette;
use App\Models\DataminingRun;
use Filament\Support\RawJs;
use Filament\Widgets\BarChartWidget;

class ClusteringBahanBakuChartWidget extends BarChartWidget
{
    protected int | string | array $columnSpan = 'full';

    public ?int $runId = null;

    public function getHeading(): string
    {
        return 'Rata-rata Jumlah Penggunaan per Klaster';
    }

    protected function getPollingInterval(): ?string
    {
        return '5s';
    }

    protected function getData(): array
    {
        $run = DataminingRun::find($this->runId) ?? DataminingRun::latestCompleted('clustering-bahan-baku');
        $summary = $run?->payload['rata_rata_table'] ?? [];

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Jumlah Penggunaan',
                    'data' => array_column($summary, 'Rata-rata Jumlah Penggunaan'),
                    'backgroundColor' => ChartPalette::colors(count($summary)),
                    'borderRadius' => 6,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => array_map(fn ($c) => 'Klaster ' . ($c['Klaster'] ?? ''), $summary),
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
