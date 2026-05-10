<?php

namespace App\Filament\Widgets;

use App\Models\DataMiningRun;
use Filament\Widgets\ChartWidget;

class FreqItemChart extends ChartWidget
{
    public ?int $recordId = null;

    protected ?string $heading = 'Frequent Items';

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'bar';
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

        $chartData = $record->result['chart_data']['freq_item'] ?? [];

        return $chartData['data'] ?? ['datasets' => [], 'labels' => []];
    }

    protected function getOptions(): ?array
    {
        $recordId = $this->recordId ?? null;
        if (!$recordId) {
            return ['indexAxis' => 'y'];
        }

        $record = DataMiningRun::find($recordId);
        if (!$record) {
            return ['indexAxis' => 'y'];
        }

        $options = $record->result['chart_data']['freq_item']['options'] ?? [];

        return array_merge(['indexAxis' => 'y'], $options);
    }
}
