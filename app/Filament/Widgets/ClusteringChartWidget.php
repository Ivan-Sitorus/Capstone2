<?php

namespace App\Filament\Widgets;

use App\Filament\Support\ChartPalette;
use App\Models\DataminingRun;
use Filament\Widgets\BarChartWidget;

class ClusteringChartWidget extends BarChartWidget
{
    protected int | string | array $columnSpan = 'full';

    public ?int $runId = null;

    public function getHeading(): string
    {
        return 'Rata-rata Jumlah Penjualan per Klaster';
    }

    protected function getPollingInterval(): ?string
    {
        return '5s';
    }

    protected function getData(): array
    {
        $run = DataminingRun::find($this->runId) ?? DataminingRun::latestCompleted('clustering');
        $summary = $run?->payload['cluster_summary'] ?? [];

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Jumlah Penjualan',
                    'data' => array_column($summary, 'Rata-rata Jumlah Penjualan'),
                    'backgroundColor' => ChartPalette::colors(count($summary)),
                    'borderRadius' => 6,
                ],
            ],
            'labels' => array_map(fn ($c) => 'Klaster ' . ($c['Klaster'] ?? ''), $summary),
        ];
    }
}
