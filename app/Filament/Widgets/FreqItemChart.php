<?php

namespace App\Filament\Widgets;

use App\Models\DataMiningRun;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class FreqItemChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Frequent Items';

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $recordId = $this->pageFilters['record_id'] ?? null;
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
        $recordId = $this->pageFilters['record_id'] ?? null;
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
