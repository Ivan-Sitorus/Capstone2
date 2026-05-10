<?php

namespace App\Filament\Widgets;

use App\Models\DataMiningRun;
use Filament\Widgets\ChartWidget;

class SilhouetteChart extends ChartWidget
{
    public ?int $recordId = null;

    protected ?string $heading = 'Silhouette Score';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $recordId = $this->recordId ?? null;
        if (!$recordId) {
            return ['datasets' => [], 'labels' => []];
        }

        $record = DataMiningRun::find($recordId);
        if (!$record) {
            return ['datasets' => [], 'labels' => []];
        }

        $chartData = $record->result['chart_data']['silhouette'] ?? [];

        return $chartData['data'] ?? ['datasets' => [], 'labels' => []];
    }

    protected function getOptions(): ?array
    {
        $recordId = $this->recordId ?? null;
        if (!$recordId) {
            return null;
        }

        $record = DataMiningRun::find($recordId);
        if (!$record) {
            return null;
        }

        return $record->result['chart_data']['silhouette']['options'] ?? null;
    }
}
