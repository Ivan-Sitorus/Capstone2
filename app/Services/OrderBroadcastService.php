<?php

namespace App\Services;

use App\Events\OrderStatusUpdated;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class OrderBroadcastService
{
    public static function broadcastPendingCount(): void
    {
        $count = Order::where('status', Order::STATUS_PENDING)
            ->where(fn($q) =>
                $q->where('order_type', 'cashier')
                  ->orWhere(fn($q2) =>
                      $q2->where('order_type', 'qr')
                         ->where(fn($q3) =>
                             $q3->where('payment_method', 'cash')
                                ->orWhere(fn($q4) => $q4->where('payment_method', 'qris')->whereNotNull('payment_proof'))
                         )
                  )
            )->count();

        // Sync cache so next page-load/polling also gets fresh value
        Cache::put('pending_order_count', $count, 30);

        broadcast(new OrderStatusUpdated('pending', $count));
    }
}
