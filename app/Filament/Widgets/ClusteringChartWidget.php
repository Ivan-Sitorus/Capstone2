<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use Filament\Widgets\BarChartWidget;

class ClusteringChartWidget extends BarChartWidget
{
    protected int | string | array $columnSpan = 'full';

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
        $run = DataminingRun::latestCompleted('clustering');
        $summary = $run?->payload['cluster_summary'] ?? [];

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Jumlah Penjualan',
                    'data' => array_column($summary, 'Rata-rata Jumlah Penjualan'),
                    'backgroundColor' => '#6366f1',
                    'borderRadius' => 6,
                ],
            ],
            'labels' => array_map(fn ($c) => 'Klaster ' . ($c['Klaster'] ?? ''), $summary),
        ];
    }
}
