<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransactionStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $today = today();
        $totalToday = (float) Order::whereDate('created_at', $today)->sum('total_amount');
        $countToday = Order::whereDate('created_at', $today)->count();
        $average = $countToday > 0 ? (int) round($totalToday / $countToday) : 0;
        $activeOrders = Order::whereIn('status', [
            \App\Enums\OrderStatus::Pending->value,
            \App\Enums\OrderStatus::Diproses->value,
            \App\Enums\OrderStatus::BelumLunas->value,
        ])->count();

        return [
            Stat::make('Penjualan Hari Ini', 'Rp ' . number_format($totalToday, 0, ',', '.'))
                ->description('Total pendapatan hari ini')
                ->color('success')
                ->icon('heroicon-o-banknotes'),
            Stat::make('Transaksi Hari Ini', $countToday)
                ->description('Jumlah order hari ini')
                ->color('info')
                ->icon('heroicon-o-shopping-cart'),
            Stat::make('Rata-rata / Transaksi', 'Rp ' . number_format($average, 0, ',', '.'))
                ->description('Nilai rata-rata per order')
                ->color('primary')
                ->icon('heroicon-o-calculator'),
            Stat::make('Pesanan Aktif', $activeOrders)
                ->description('Pending, diproses, belum lunas')
                ->color('warning')
                ->icon('heroicon-o-clock'),
        ];
    }
}
