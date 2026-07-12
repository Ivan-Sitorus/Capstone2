<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ChooseQrisAction
{
    public function handle(Order $order): JsonResponse
    {
        if ($order->status !== OrderStatus::Pending->value) {
            return response()->json(['message' => 'Status pesanan tidak valid.'], 409);
        }
        $order->update(['payment_method' => 'qris']);

        [$qrisImage, $qrisName] = Cache::remember('qris_settings', 600, fn() => [
            asset('storage/' . Setting::get('qris_image', 'qris/qris-w9cafe.png')),
            Setting::get('qris_name', 'W9 Cafe'),
        ]);

        return response()->json([
            'qris_image'   => $qrisImage,
            'qris_name'    => $qrisName,
            'total_amount' => $order->total_amount,
        ]);
    }
}
