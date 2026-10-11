<?php

namespace App\Filament\Widgets;

use App\Models\DataminingRun;
use App\Support\Formatter;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DataminingRunStatsWidget extends StatsOverviewWidget
{
    protected int | array | null $columns = 4;

    public ?int $runId = null;

    protected function getStats(): array
    {
        $run = DataminingRun::find($this->runId);
        $payload = $run?->payload ?? [];

        $stats = [];

        if (array_key_exists('total_menu', $payload)) {
            $stats[] = Stat::make('Jumlah Menu', Formatter::number((int) $payload['total_menu']));
        }

        if (array_key_exists('total_ingredients', $payload)) {
            $stats[] = Stat::make('Jumlah Bahan Baku', Formatter::number((int) $payload['total_ingredients']));
        }

        if (array_key_exists('best_k', $payload)) {
            $stats[] = Stat::make('K Optimal', Formatter::number((int) $payload['best_k']))
                ->description('Jumlah klaster terbaik');
        }

        if (array_key_exists('silhouette_score', $payload)) {
            $stats[] = Stat::make('Silhouette Score', number_format((float) $payload['silhouette_score'], 4, ',', '.'));
        }

        if (array_key_exists('total_rules', $payload)) {
            $stats[] = Stat::make('Jumlah Rules', Formatter::number((int) $payload['total_rules']));
        }

        if (array_key_exists('total_transactions', $payload)) {
            $stats[] = Stat::make('Jumlah Transaksi', Formatter::number((int) $payload['total_transactions']));
        }

        return $stats;
    }
}
