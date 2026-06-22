<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;

class CashierPendingCountController extends Controller
{
    public function __invoke()
    {
        // Satu sumber kebenaran — sama dengan badge sidebar & broadcast
        $count = Order::cashierPendingCount();

        return response()->json(['count' => $count])
            ->header('Cache-Control', 'no-store');
    }
}
