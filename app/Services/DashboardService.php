<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Models\Order;

class DashboardService
{
    /**
     * Tanpa cache — dashboard dimuat ulang fresh tiap dibuka (reload-on-mount),
     * query difilter "hari ini" + index created_at sehingga tetap ringan.
     */
    public function getTodayStats(): object
    {
        return Order::whereDate('created_at', today())
            ->selectRaw("
                SUM(CASE WHEN status = ? THEN total_amount ELSE 0 END) AS total_penjualan,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END)            AS jumlah_transaksi,
                SUM(CASE WHEN status = ? AND payment_method = ? THEN 1 ELSE 0 END) AS cash_pending,
                SUM(CASE WHEN status = ? AND payment_method = ? AND payment_proof IS NOT NULL THEN 1 ELSE 0 END) AS qris_pending
            ", [
            OrderStatus::Completed->value,
            OrderStatus::Completed->value,
            OrderStatus::Pending->value,
            PaymentMethod::Cash->value,
            OrderStatus::Pending->value,
            PaymentMethod::Qris->value,
            ])
            ->first();
    }

    public function getActiveOrdersCount(): int
    {
        return Order::whereNotIn('status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value])
            ->where(fn($q) =>
                $q->where('order_type', OrderType::Cashier->value)
                  ->orWhere(fn($q2) =>
                      $q2->where('order_type', OrderType::Qr->value)
                         ->where(fn($q3) =>
                             $q3->where('payment_method', PaymentMethod::Cash->value)
                                ->orWhere(fn($q4) => $q4->where('payment_method', PaymentMethod::Qris->value)->whereNotNull('payment_proof'))
                         )
                  )
            )->count();
    }

    public function getRecentTransactions(): array
    {
        return Order::with(['items' => fn($q) => $q->select('id', 'order_id', 'menu_id', 'quantity')->with(['menu' => fn($q) => $q->select('id', 'name')])])
            ->select('id', 'order_code', 'customer_name', 'total_amount', 'payment_method', 'status', 'created_at')
            ->whereDate('created_at', today())
            ->whereNotNull('payment_method')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($o) => [
                'id'             => $o->id,
                'order_code'     => $o->order_code,
                'customer_name'  => $o->customer_name,
                'itemsSummary'  => $o->items->map(fn($i) => $i->quantity . 'x ' . $i->menu->name)->join(', '),
                'total_amount'   => $o->total_amount,
                'payment_method' => $o->payment_method,
                'status'         => $o->status,
            ])
            ->values()
            ->all();
    }
}
