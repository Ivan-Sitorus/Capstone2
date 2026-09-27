<?php

namespace App\Http\Controllers\Customer;

use App\Actions\PlaceCustomerOrderAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Exceptions\MenuUnavailableException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\CustomerSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerOrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            return app(PlaceCustomerOrderAction::class)->handle($request);
        } catch (MenuUnavailableException $e) {
            // A menu that was deactivated/deleted after it was cached on the
            // device must surface as a conflict, not a 500.
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function history(Request $request): Response
    {
        // The phone comes from the server-side session identity, never from the
        // request. An attacker-controlled `?phone=` query parameter is ignored
        // so the endpoint cannot be used to read another customer's orders.
        $phone = CustomerSession::phone();

        $orders = $phone
            ? Order::with([
                'items'      => fn($q) => $q->select(['id', 'order_id', 'menu_id', 'quantity', 'subtotal']),
                'items.menu' => fn($q) => $q->select(['id', 'name']),
            ])
                ->select(['id', 'order_code', 'status', 'total_amount', 'created_at', 'payment_method', 'customer_name', 'phone', 'payment_proof'])
                ->where('phone', $phone)
                ->whereNot(function ($q) {
                    // Hide QRIS orders without proof that have not been confirmed by cashier
                    $q->where('payment_method', PaymentMethod::Qris->value)
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
                    'itemsSummary'  => $o->items
                        ->map(fn($i) => "{$i->quantity}x ".($i->menu?->name ?? 'Menu dihapus'))
                        ->join(', '),
                    'items' => $o->items->map(fn($i) => [
                        'name'     => $i->menu?->name ?? 'Menu dihapus',
                        'quantity' => $i->quantity,
                        'subtotal' => (float) $i->subtotal,
                    ])->values()->toArray(),
                ])
            : collect();

        return Inertia::render('Customer/History/Index', ['orders' => $orders]);
    }

    public function status(string $code): Response
    {
        $order = Order::select(['id', 'order_code', 'status', 'total_amount', 'payment_method', 'created_at'])
            ->where('order_code', $code)
            ->firstOrFail();

        return Inertia::render('Customer/Order/Status', [
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
