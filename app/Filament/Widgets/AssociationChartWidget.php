<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use Filament\Widgets\BarChartWidget;

class AssociationChartWidget extends BarChartWidget
{
    protected int | string | array $columnSpan = 'full';

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
        $run = DataminingRun::latestCompleted('association');
        $rules = $run?->payload['rules'] ?? [];

        return [
            'datasets' => [
                [
                    'label' => 'Lift',
                    'data' => array_column($rules, 'lift'),
                    'backgroundColor' => '#6366f1',
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
