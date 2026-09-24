<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use Filament\Widgets\LineChartWidget;

class SilhouetteChartWidget extends LineChartWidget
{
    protected int | string | array $columnSpan = 1;

    public ?int $runId = null;

    public function getHeading(): string
    {
        return 'Silhouette Score per K';
    }

    protected function getData(): array
    {
        $run = DataminingRun::find($this->runId);
        $curve = $run?->payload['silhouette_curve'] ?? [];

        return [
            'datasets' => [
                [
                    'label' => 'Silhouette Score',
                    'data' => array_map('floatval', $curve['score'] ?? []),
                    'borderColor' => '#059669',
                    'backgroundColor' => 'rgba(5, 150, 105, 0.1)',
                    'fill' => false,
                ],
            ],
            'labels' => array_map('strval', $curve['k'] ?? []),
        ];
    }
}
