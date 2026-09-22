<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use Filament\Widgets\BarChartWidget;

class ClusteringBahanBakuChartWidget extends BarChartWidget
{
    protected int | string | array $columnSpan = 'full';

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
        $run = DataminingRun::latestCompleted('clustering-bahan-baku');
        $summary = $run?->payload['rata_rata_table'] ?? [];

        return [
            'datasets' => [
                [
                    'label' => 'Rata-rata Jumlah Penggunaan',
                    'data' => array_column($summary, 'Rata-rata Jumlah Penggunaan'),
                    'backgroundColor' => '#6366f1',
                    'borderRadius' => 6,
                ],
            ],
            'labels' => array_map(fn ($c) => 'Klaster ' . ($c['Klaster'] ?? ''), $summary),
        ];
    }
}
