<?php

namespace App\Actions;

use App\Enums\MenuStatus;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Exceptions\MenuUnavailableException;
use App\Models\CafeTable;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\CustomerSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PlaceCustomerOrderAction
{
    public function handle(Request $request): JsonResponse
    {
        // The mobile cart posts `customer_phone` while other callers use
        // `phone`; accept both so the number is actually persisted and can bind
        // the session identity to this order.
        $request->merge([
            'phone' => $request->input('phone', $request->input('customer_phone')),
        ]);

        $request->validate([
            'customer_name'     => 'required|string|min:2|max:255',
            'phone'             => ['nullable', 'string', 'regex:/^[0-9]{10,15}$/'],
            'table_id'          => 'required|integer|exists:cafe_tables,id',
            'is_mahasiswa'      => 'boolean',
            'items'             => 'required|array|min:1',
            'items.*.menu_id'   => 'required|integer|exists:menus,id',
            'items.*.quantity'  => 'required|integer|min:1|max:20',
        ], [
            'customer_name.required' => 'Nama wajib diisi.',
            'phone.regex'            => 'Nomor telepon tidak valid.',
            'items.required'         => 'Pesanan tidak boleh kosong.',
            'items.min'              => 'Minimal 1 item dalam pesanan.',
        ]);

        return DB::transaction(function () use ($request) {
            Cache::remember("cafe_table_{$request->table_id}", 600, fn() =>
                CafeTable::findOrFail($request->table_id)
            );

            $isMahasiswa = (bool) $request->input('is_mahasiswa', false);

            $order = Order::create([
                'customer_name'  => $request->customer_name,
                'phone'          => $request->phone,
                'table_id'       => $request->table_id,
                'cashier_id'     => null,
                'order_type'     => 'qr',
                'status'         => OrderStatus::Pending->value,
                'total_amount'   => 0,
            ]);

            $total = 0;
            $orderItemsToInsert = [];

            $menuIds = collect($request->items)->pluck('menu_id')->unique()->all();
            // Always read the menus fresh inside the transaction and lock them:
            // a cached model could still report an inactive menu as available.
            $menus = Menu::whereIn('id', $menuIds)->lockForUpdate()->get()->keyBy('id');

            foreach ($request->items as $position => $item) {
                $menu = $menus->get($item['menu_id']);

                if (!$menu || $menu->status !== MenuStatus::Active) {
                    throw new MenuUnavailableException(
                        "Menu " . ($menu?->name ?? "#{$item['menu_id']}") . " tidak tersedia."
                    );
                }

                $unitPrice = ($isMahasiswa && $menu->is_student_discount && $menu->student_price !== null)
                    ? $menu->student_price
                    : $menu->price;
                $subtotal = $unitPrice * (int) $item['quantity'];

                $orderItemsToInsert[] = [
                    'order_id'   => $order->id,
                    'menu_id'    => $menu->id,
                    'item_position' => $position,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'cost_price' => (int) $menu->cost_price,
                    'subtotal'   => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $total += $subtotal;
            }

            OrderItem::insert($orderItemsToInsert);

            $order->update(['total_amount' => $total]);

            // Bind the anonymous customer identity to this browser session so
            // later history/payment/status requests can be authorised against
            // it instead of trusting client-supplied identifiers.
            CustomerSession::bind($order->customer_name, $order->phone);
            CustomerSession::rememberOrder($order);

            return response()->json([
                'order_code'   => $order->order_code,
                'total_amount' => $order->total_amount,
                'order_id'     => $order->id,
            ], 201);
        }, 3);
    }
}
