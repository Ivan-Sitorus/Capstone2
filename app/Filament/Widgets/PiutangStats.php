<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PiutangStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $piutangOrders = Order::with('orderPayments')
            ->where(function ($query) {
                $query->where('payment_method', 'piutang')
                    ->orWhere('status', OrderStatus::BelumLunas->value);
            })
            ->get();

        $totalRemaining = 0;
        foreach ($piutangOrders as $order) {
            $paid = (float) $order->orderPayments->sum('amount');
            $totalRemaining += max(0, (float) $order->total_amount - $paid);
        }

        return [
            Stat::make('Total Piutang', 'Rp ' . number_format($totalRemaining, 0, ',', '.'))
                ->description('Sisa tagihan belum lunas')
                ->color('warning')
                ->icon('heroicon-o-currency-dollar'),
            Stat::make('Piutang Aktif', $piutangOrders->count())
                ->description('Jumlah order belum lunas')
                ->color('danger')
                ->icon('heroicon-o-document-text'),
        ];
    }
}
