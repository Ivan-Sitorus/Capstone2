<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use Filament\Widgets\BarChartWidget;

class PredictionChartWidget extends BarChartWidget
{
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        return 'Total Prediksi Penjualan per Menu (2 hari ke depan)';
    }

    protected function getPollingInterval(): ?string
    {
        return '5s';
    }

    protected function getData(): array
    {
        $run = DataminingRun::latestCompleted('prediction');
        $summary = $run?->payload['summary_table'] ?? [];

        return [
            'datasets' => [
                [
                    'label' => 'Total Prediksi (unit)',
                    'data' => array_column($summary, 'total_forecast'),
                    'backgroundColor' => '#6366f1',
                    'borderRadius' => 6,
                ],
            ],
            'labels' => array_column($summary, 'nama_menu'),
        ];
    }
}
