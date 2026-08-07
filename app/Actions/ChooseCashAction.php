<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ChooseCashAction
{
    public function handle(Order $order): JsonResponse
    {
        if ($order->status !== OrderStatus::Pending) {
            return response()->json(['message' => 'Status pesanan tidak valid.'], 409);
        }
        DB::transaction(function () use ($order) {
            $order->update([
                'payment_method' => 'cash',
                'order_code'     => Order::generateCode(),
            ]);
        });
        return response()->json(['message' => 'ok', 'order_code' => $order->fresh()->order_code]);
    }
}
