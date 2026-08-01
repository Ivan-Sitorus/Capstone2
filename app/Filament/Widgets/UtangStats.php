<?php

namespace App\Filament\Widgets;

use App\Models\IngredientBatch;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UtangStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $unpaidBatches = IngredientBatch::with('batchPayments')
            ->where('payment_status', 'belum_lunas')
            ->where('total_cost', '>', 0)
            ->get();

        $totalRemaining = 0;
        $suppliers = [];
        foreach ($unpaidBatches as $batch) {
            $paid = (float) $batch->batchPayments->sum('amount');
            $totalRemaining += max(0, (float) $batch->total_cost - $paid);
            if ($batch->supplier_name) {
                $suppliers[$batch->supplier_name] = true;
            }
        }

        return [
            Stat::make('Total Utang Supplier', 'Rp ' . number_format($totalRemaining, 0, ',', '.'))
                ->description('Sisa utang bahan baku')
                ->color('danger')
                ->icon('heroicon-o-truck'),
            Stat::make('Supplier Belum Lunas', count($suppliers))
                ->description('Jumlah supplier berutang')
                ->color('warning')
                ->icon('heroicon-o-users'),
        ];
    }
}
