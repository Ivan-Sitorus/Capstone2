<?php

namespace App\Http\Controllers\Cashier;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CashierOrderHistoryController extends Controller
{
    public function index(Request $request): Response
    {
        // `date` reaches a whereDate() raw-ish comparison, so validate it before
        // use; an arbitrary string would otherwise hit the database as-is.
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'date'],
            'method' => ['nullable', 'in:cash,qris,pay_later'],
        ]);

        $orders = Order::with(['cashier' => fn ($q) => $q->select('id', 'name')])
            ->select('id', 'order_code', 'cashier_id', 'customer_name', 'total_amount', 'payment_method', 'status', 'created_at')
            ->whereIn('status', [OrderStatus::Completed->value, OrderStatus::Cancelled->value])
            ->when($request->search, fn ($q) => $q->where('order_code', 'like', '%'.$request->search.'%')
                ->orWhere('customer_name', 'like', '%'.$request->search.'%'))
            ->when($request->date, fn ($q) => $q->whereDate('created_at', $request->date))
            ->when($request->input('method'), fn ($q) => $q->where('payment_method', $request->input('method')))
            ->latest()
            ->paginate(25)
            ->through(fn ($o) => [
                'id' => $o->id,
                'order_code' => $o->order_code,
                'created_at' => $o->created_at->toISOString(),
                'total_amount' => $o->total_amount,
                'payment_method' => $o->payment_method,
                'cashier_name' => $o->cashier?->name,
                'customer_name' => $o->customer_name,
                'status' => $o->status,
            ]);

        return Inertia::render('Cashier/OrderHistory', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'date', 'method']),
        ]);
    }
}
