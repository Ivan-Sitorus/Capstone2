<?php

namespace App\Filament\Widgets;

use App\Models\DataMiningRun;
use Filament\Widgets\ChartWidget;

class ClusteringBarChart extends ChartWidget
{
    public ?int $recordId = null;

    protected ?string $heading = 'Penjualan per Menu';

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

        $chartData = $record->result['chart_data']['bar'] ?? [];

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

        return $record->result['chart_data']['bar']['options'] ?? null;
    }
}
