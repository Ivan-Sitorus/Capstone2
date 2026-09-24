<?php

namespace App\Filament\Widgets;

use App\Filament\Support\ChartPalette;
use App\Models\DataminingRun;
use Filament\Widgets\BarChartWidget;

class FeatureImportanceChartWidget extends BarChartWidget
{
    protected int | string | array $columnSpan = 'full';

    public ?int $runId = null;

    public function getHeading(): string
    {
        return 'Rata-rata Weekday vs Weekend';
    }

    protected function getData(): array
    {
        $run = DataminingRun::find($this->runId);
        $rows = $run?->payload['feature_importance'] ?? [];

        $labels = array_map(fn (array $r): string => (string) ($r['item'] ?? $r['bahan'] ?? ''), $rows);

        return [
            'datasets' => [
                [
                    'label' => 'Weekday',
                    'data' => array_map(fn (array $r): float => (float) ($r['Weekday'] ?? 0), $rows),
                    'backgroundColor' => ChartPalette::colors(count($rows)),
                ],
                [
                    'label' => 'Weekend',
                    'data' => array_map(fn (array $r): float => (float) ($r['Weekend'] ?? 0), $rows),
                    'backgroundColor' => '#E8692A',
                ],
            ],
            'labels' => $labels,
        ];
    }
}
