<?php

namespace App\Http\Controllers\Cashier;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class CashierPesananAktifController extends Controller
{
    public function index(): Response
    {
        $orders = Order::with(['items.menu', 'cafeTable', 'cashier'])
            ->whereNotIn('status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value])
            ->where('status', '!=', OrderStatus::Unpaid->value)
            ->where(function ($q) {
                // Orders from cashier: always shown
                $q->where('order_type', 'cashier')
                  // Orders from customer via QR:
                    ->orWhere(fn ($q2) => $q2->where('order_type', 'qr')
                        ->where(fn ($q3) =>
                            // Cash: shown as soon as selected
                            $q3->where('payment_method', 'cash')
                               // QRIS: shown when proof is submitted (pending) OR confirmed (processing, proof removed)
                                ->orWhere(fn ($q4) => $q4->where('payment_method', 'qris')
                                    ->where(fn ($q5) => $q5->whereNotNull('payment_proof')
                                        ->orWhere('status', OrderStatus::Processing->value)
                                    )
                                )
                        )
                    );
            })
            ->latest()
            ->get();

        $counts = [
            'all' => $orders->count(),
'pending' => $orders->where('status', OrderStatus::Pending->value)->count(),
                'processing' => $orders->where('status', OrderStatus::Processing->value)->count(),
            'belum_bayar' => $orders->where('payment_method', 'pay_later')->count(),
        ];

        $ordersData = $orders->map(fn ($o) => [
            'id' => $o->id,
            'order_code' => $o->order_code,
            'status' => $o->status,
            'payment_method' => $o->payment_method,
            'customer_name' => $o->customer_name,
            'table_number' => $o->cafeTable?->table_number,
            'created_at' => $o->created_at->toISOString(),
            'items_summary' => $o->items->map(fn ($i) => $i->quantity.'x '.$i->menu->name)->join(', '),
            'total_amount' => $o->total_amount,
            'payment_proof' => $o->payment_proof ? asset('storage/'.$o->payment_proof) : null,
            'rejection_note' => $o->rejection_note,
            'items' => $o->items->map(fn ($i) => [
                'name' => $i->menu->name,
                'quantity' => $i->quantity,
                'unit_price' => $i->unit_price,
                'subtotal' => $i->subtotal,
            ]),
        ]);

        return Inertia::render('Kasir/PesananAktif', [
            'orders' => $ordersData,
            'counts' => $counts,
        ]);
    }
}
