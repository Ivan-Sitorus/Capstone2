<?php

namespace App\Http\Controllers\Customer;

use App\Actions\PlaceCustomerOrderAction;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerOrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        return app(PlaceCustomerOrderAction::class)->handle($request);
    }

    public function riwayat(Request $request): Response
    {
        // Riwayat berdasarkan nomor telepon di sessionStorage (dikirim via query param)
        $phone = $request->query('phone');

        $orders = $phone
            ? Order::with([
                'items'      => fn($q) => $q->select(['id', 'order_id', 'menu_id', 'quantity', 'subtotal']),
                'items.menu' => fn($q) => $q->select(['id', 'name']),
            ])
                ->select(['id', 'order_code', 'status', 'total_amount', 'created_at', 'payment_method', 'customer_name', 'phone', 'payment_proof'])
                ->where('phone', $phone)
                ->whereNot(function ($q) {
                    // Sembunyikan QRIS yang belum ada bukti & belum dikonfirmasi kasir
                    $q->where('payment_method', 'qris')
                      ->whereNull('payment_proof')
                      ->whereNotIn('status', [OrderStatus::Processing->value, OrderStatus::Completed->value]);
                })
                ->latest()
                ->limit(50)
                ->get()
                ->map(fn($o) => [
                    'id'             => $o->id,
                    'order_code'     => $o->order_code,
                    'status'         => $o->status,
                    'total_amount'   => $o->total_amount,
                    'created_at'     => $o->created_at->toISOString(),
                    'payment_method' => $o->payment_method,
                    'customer_name'  => $o->customer_name,
                    'items_summary'  => $o->items
                        ->map(fn($i) => "{$i->quantity}x {$i->menu->name}")
                        ->join(', '),
                    'items' => $o->items->map(fn($i) => [
                        'name'     => $i->menu->name,
                        'quantity' => $i->quantity,
                        'subtotal' => (float) $i->subtotal,
                    ])->values()->toArray(),
                ])
            : collect();

        return Inertia::render('Pelanggan/Riwayat/Index', ['orders' => $orders]);
    }

    public function status(string $code): Response
    {
        $order = Order::select(['id', 'order_code', 'status', 'total_amount', 'payment_method', 'created_at'])
            ->where('order_code', $code)
            ->firstOrFail();

        return Inertia::render('Pelanggan/Order/Status', [
            'order' => [
                'id'             => $order->id,
                'order_code'     => $order->order_code,
                'status'         => $order->status,
                'total_amount'   => $order->total_amount,
                'payment_method' => $order->payment_method,
                'created_at'     => $order->created_at->toISOString(),
            ],
        ]);
    }

    public function showStatus(Order $order): Response
    {
        return $this->status($order->order_code);
    }
}
