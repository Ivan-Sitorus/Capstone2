<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Setting;
use Inertia\Inertia;
use Inertia\Response;

class ReceiptController extends Controller
{
    public function show(string $orderCode): Response
    {
        $order = Order::with(['items.menu', 'cafeTable', 'cashier'])
            ->where('order_code', $orderCode)
            ->firstOrFail();

        return $this->renderReceipt($order);
    }

    public function showByReceiptToken(Order $order): Response
    {
        $order->load(['items.menu', 'cafeTable', 'cashier']);

        return $this->renderReceipt($order);
    }

    private function renderReceipt(Order $order): Response
    {
        // Calculate discount (difference between unit_price * qty and subtotal)
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
                'receipt_token' => $order->receipt_token,
                'order_code' => $order->order_code,
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'customer_name' => $order->customer_name,
                'phone' => $order->phone,
                'payment_method' => $order->payment_method,
                'created_at' => $order->created_at->toISOString(),
                'cashier_name' => $order->cashier?->name,
                'table_number' => $order->cafeTable?->table_number,
                'items' => $items,
                'discount' => $discount > 0 ? $discount : 0,
                'raw_total' => $rawTotal,
            ],
            'cafe' => [
                'receipt_title' => Setting::get('receipt_title') ?? '',
                'receipt_header' => Setting::get('receipt_header') ?? '',
                'receipt_footer' => Setting::get('receipt_footer') ?? '',
            ],
            'receiptUrl' => $order->receipt_url,
        ]);
    }
}
