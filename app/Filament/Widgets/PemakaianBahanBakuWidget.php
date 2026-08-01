<?php

namespace App\Filament\Widgets;

use App\Models\DailyIngredientUsage;
use Filament\Widgets\BarChartWidget;

class PemakaianBahanBakuWidget extends BarChartWidget
{
    public function getHeading(): string
    {
        return 'Pemakaian Bahan Baku (7 Hari Terakhir)';
    }

    protected function getData(): array
    {
        $rows = DailyIngredientUsage::query()
            ->where('usage_date', '>=', now()->subDays(7)->toDateString())
            ->orderByDesc('jumlah_digunakan')
            ->limit(10)
            ->get(['ingredient_name', 'unit', 'jumlah_digunakan']);

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Dipakai',
                    'data' => $rows->map(fn ($r) => (float) $r->jumlah_digunakan)->values()->all(),
                    'backgroundColor' => '#3B6FD4',
                ],
            ],
            'labels' => $rows->map(fn ($r) => $r->ingredient_name . ($r->unit ? ' (' . $r->unit . ')' : ''))->values()->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
