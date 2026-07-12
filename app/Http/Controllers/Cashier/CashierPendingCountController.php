<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class CashierPendingCountController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(['count' => Order::cashierPendingCount()])
            ->header('Cache-Control', 'no-store');
    }
}
