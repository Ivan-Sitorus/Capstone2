<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class CashierPendingCountController extends Controller
{
    public function __invoke()
    {
        $count = Cache::remember('pending_order_count', 30, fn() =>
            Order::where('status', Order::STATUS_PENDING)
                ->where(fn($q) =>
                    $q->where('order_type', 'cashier')
                      ->orWhere(fn($q2) =>
                          $q2->where('order_type', 'qr')
                             ->where(fn($q3) =>
                                 $q3->where('payment_method', 'cash')
                                    ->orWhere(fn($q4) => $q4->where('payment_method', 'qris')->whereNotNull('payment_proof'))
                             )
                      )
                )->count()
        );

        return response()->json(['count' => $count])
            ->header('Cache-Control', 'no-store'); // client tidak cache, tapi server sudah
    }
}
