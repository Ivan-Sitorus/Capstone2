<?php

namespace App\Filament\Widgets;

use App\Models\DataMiningRun;
use Filament\Widgets\ChartWidget;

class SupConfChart extends ChartWidget
{
    public ?int $recordId = null;

    protected ?string $heading = 'Support vs Confidence';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'scatter';
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

        $chartData = $record->result['chart_data']['sup_conf'] ?? [];
        $data = $chartData['data'] ?? ['datasets' => [], 'labels' => []];
        foreach ($data['datasets'] as &$dataset) {
            if (!isset($dataset['backgroundColor'])) {
                $dataset['backgroundColor'] = match ($this->getType()) {
                    'bar' => '#3b82f6',
                    'line' => 'transparent',
                    'scatter' => '#3b82f6',
                    default => '#3b82f6',
                };
            }
            if (!isset($dataset['borderColor'])) {
                $dataset['borderColor'] = '#1d4ed8';
            }
        }
        return $data;
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

        return $record->result['chart_data']['sup_conf']['options'] ?? null;
    }
}
