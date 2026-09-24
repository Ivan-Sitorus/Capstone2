<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
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
}
