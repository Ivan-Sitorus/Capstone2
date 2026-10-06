<?php

namespace App\Filament\Widgets;

use App\Filament\Support\ChartPalette;
use App\Models\DataminingRun;
use Filament\Support\RawJs;
use Filament\Widgets\BarChartWidget;

class PredictionBahanBakuChartWidget extends BarChartWidget
{
    protected int | string | array $columnSpan = 'full';

    public ?int $runId = null;

    public function getHeading(): string
    {
        return 'Total Prediksi Penggunaan Bahan Baku (2 hari ke depan)';
    }

    protected function getPollingInterval(): ?string
    {
        return '5s';
    }

    protected function getData(): array
    {
        $run = DataminingRun::find($this->runId) ?? DataminingRun::latestCompleted('prediction-bahan-baku');
        $summary = $run?->payload['summary_table'] ?? [];

        return [
            'datasets' => [
                [
                    'label' => 'Total Prediksi (satuan)',
                    'data' => array_column($summary, 'total_forecast'),
                    'backgroundColor' => ChartPalette::colors(count($summary)),
                    'borderRadius' => 6,
                    'borderWidth' => 0,
                ],
            ],
            'labels' => array_column($summary, 'nama_bahan_baku'),
        ];
    }

    protected function getOptions(): array | RawJs | null
    {
        $nf = ChartPalette::idNumberFormat();

        return RawJs::make(<<<JS
            {
                interaction: { mode: 'index', intersect: false },
                hover: { mode: 'index', intersect: false },
                scales: {
                    y: {
                        ticks: {
                            callback: (value) => {$nf}.format(value),
                        },
                    },
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ctx.dataset.label + ': ' + {$nf}.format(ctx.parsed.y),
                        },
                    },
                },
            }
        JS);
    }
}
