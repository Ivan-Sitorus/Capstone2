<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use Inertia\Inertia;

class ReceiptController extends Controller
{
    public function show(string $orderCode)
    {
        $order = Order::with(['items.menu', 'cafeTable', 'cashier'])
            ->where('order_code', $orderCode)
            ->firstOrFail();

        // Hitung diskon (selisih total dari unit_price * qty vs subtotal)
        $items = $order->items->map(fn ($i) => [
            'name' => $i->menu->name,
            'unit_price' => $i->unit_price,
            'quantity' => $i->quantity,
            'subtotal' => $i->subtotal,
        ]);

        $rawTotal = $order->items->sum(fn ($i) => $i->unit_price * $i->quantity);
        $discount = $rawTotal - $order->total_amount;

        return Inertia::render('Receipt/Show', [
            'order' => [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'payment_method' => $order->payment_method,
                'is_paid' => $order->is_paid,
                'created_at' => $order->created_at->toISOString(),
                'cashier_name' => $order->cashier?->name,
                'table_number' => $order->cafeTable?->table_number,
                'items' => $items,
                'discount' => $discount > 0 ? $discount : 0,
                'raw_total' => $rawTotal,
            ],
            'cafe' => [
                'name' => Setting::get('cafe_name', 'W9 Cafe'),
                'address' => Setting::get('cafe_address', 'STIE Totalwin Semarang'),
                'phone' => Setting::get('cafe_phone', ''),
            ],
        ]);
    }
}
