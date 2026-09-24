<?php

namespace App\Filament\Widgets;

use App\Filament\Support\ChartPalette;
use App\Models\DataminingRun;
use Filament\Widgets\BarChartWidget;

class AssociationChartWidget extends BarChartWidget
{
    protected int | string | array $columnSpan = 'full';

    public ?int $runId = null;

    public function getHeading(): string
    {
        return 'Lift Association Rules (Top)';
    }

    protected function getPollingInterval(): ?string
    {
        return '5s';
    }

    protected function getData(): array
    {
        $run = DataminingRun::find($this->runId) ?? DataminingRun::latestCompleted('association');
        $rules = $run?->payload['rules'] ?? [];

        return [
            'datasets' => [
                [
                    'label' => 'Lift',
                    'data' => array_column($rules, 'lift'),
                    'backgroundColor' => ChartPalette::colors(count($rules)),
                    'borderRadius' => 6,
                ],
            ],
            'labels' => array_map(
                fn ($r) => ($r['menu_pertama'] ?? '') . ' → ' . ($r['menu_kedua'] ?? ''),
                $rules
            ),
        ];
    }
}
