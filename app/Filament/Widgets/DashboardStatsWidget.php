<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\IngredientBatch;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class DashboardStatsWidget extends StatsOverviewWidget
{
    protected int | array | null $columns = 4;

    protected function getStats(): array
    {
        $today = today();

        $totalToday = (float) Order::whereDate('created_at', $today)->sum('total_amount');
        $countToday = Order::whereDate('created_at', $today)->count();

        $piutangOrders = Order::with('orderPayments')
            ->where(function ($query) {
                $query->where('payment_method', 'piutang')
                    ->orWhere('status', OrderStatus::BelumLunas->value);
            })
            ->get();
        $totalPiutang = 0;
        foreach ($piutangOrders as $order) {
            $paid = (float) $order->orderPayments->sum('amount');
            $totalPiutang += max(0, (float) $order->total_amount - $paid);
        }

        $unpaidBatches = IngredientBatch::with('batchPayments')
            ->where('payment_status', 'belum_lunas')
            ->where('total_cost', '>', 0)
            ->get();
        $totalUtang = 0;
        $supplierCount = 0;
        $suppliers = [];
        foreach ($unpaidBatches as $batch) {
            $paid = (float) $batch->batchPayments->sum('amount');
            $totalUtang += max(0, (float) $batch->total_cost - $paid);
            if ($batch->supplier_name) {
                $suppliers[$batch->supplier_name] = true;
            }
        }
        $supplierCount = count($suppliers);

        $stokMenipis = IngredientBatch::query()
            ->whereNotNull('initial_quantity')
            ->where('quantity', '>', 0)
            ->whereColumn('quantity', '<', DB::raw('initial_quantity * 0.2'))
            ->distinct('ingredient_id')
            ->count('ingredient_id');
        $stokExpired = IngredientBatch::query()
            ->where('quantity', '>', 0)
            ->whereDate('expiry_date', '<', today()->toDateString())
            ->distinct('ingredient_id')
            ->count('ingredient_id');

        return [
            Stat::make('Penjualan Hari Ini', 'Rp ' . number_format($totalToday, 0, ',', '.'))
                ->description('Total pendapatan hari ini')
                ->color('success')
                ->icon('heroicon-o-banknotes'),
            Stat::make('Transaksi Hari Ini', $countToday)
                ->description('Jumlah order hari ini')
                ->color('info')
                ->icon('heroicon-o-shopping-cart'),
            Stat::make('Total Piutang', 'Rp ' . number_format($totalPiutang, 0, ',', '.'))
                ->description('Sisa tagihan belum lunas')
                ->color('warning')
                ->icon('heroicon-o-currency-dollar'),
            Stat::make('Piutang Aktif', $piutangOrders->count())
                ->description('Jumlah order belum lunas')
                ->color('danger')
                ->icon('heroicon-o-document-text'),
            Stat::make('Total Utang Supplier', 'Rp ' . number_format($totalUtang, 0, ',', '.'))
                ->description('Sisa utang bahan baku')
                ->color('danger')
                ->icon('heroicon-o-truck'),
            Stat::make('Supplier Belum Lunas', $supplierCount)
                ->description('Jumlah supplier berutang')
                ->color('warning')
                ->icon('heroicon-o-users'),
            Stat::make('Stok Menipis', $stokMenipis)
                ->description('Bahan baku dengan stok < 20%')
                ->color($stokMenipis > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
            Stat::make('Stok Kedaluwarsa', $stokExpired)
                ->description('Bahan baku dengan batch sudah kedaluwarsa')
                ->color($stokExpired > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-calendar-days'),
        ];
    }
}
