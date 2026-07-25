<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use Exception;
use App\Models\CafeTable;
use App\Models\Menu;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PlaceCustomerOrderAction
{
    public function handle(Request $request): JsonResponse
    {
        $request->validate([
            'customer_name'     => 'required|string|min:2|max:255',
            'customer_phone'    => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'table_id'          => 'required|integer|exists:cafe_tables,id',
            'is_mahasiswa'      => 'boolean',
            'items'             => 'required|array|min:1',
            'items.*.menu_id'   => 'required|integer|exists:menus,id',
            'items.*.quantity'  => 'required|integer|min:1|max:20',
        ], [
            'customer_name.required' => 'Nama wajib diisi.',
            'customer_phone.regex'   => 'Nomor telepon tidak valid.',
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
                'customer_phone' => $request->customer_phone,
                'table_id'       => $request->table_id,
                'cashier_id'     => null,
                'order_type'     => 'qr',
                'status'         => OrderStatus::Pending->value,
                'total_amount'   => 0,
            ]);

            $total = 0;
            $orderItemsToInsert = [];

            $menuIds = collect($request->items)->pluck('menu_id')->unique()->all();
            $menus   = collect(Cache::many(array_map(fn($id) => "menu_{$id}", $menuIds)))
                ->filter()
                ->mapWithKeys(fn($m, $k) => [str_replace('menu_', '', $k) => $m]);

            $missingIds = collect($menuIds)->filter(fn($id) => !$menus->has($id))->values()->all();
            if (!empty($missingIds)) {
                $fresh = Menu::whereIn('id', $missingIds)->get()->keyBy('id');
                foreach ($fresh as $id => $menu) {
                    Cache::put("menu_{$id}", $menu, 300);
                }
                $menus = $menus->union($fresh);
            }

            foreach ($request->items as $item) {
                $menu = $menus->get($item['menu_id']);

                if (!$menu || $menu->status !== 'active') {
                    throw new Exception("Menu " . ($menu?->name ?? "#{$item['menu_id']}") . " tidak tersedia.");
                }

                $unitPrice = ($isMahasiswa && $menu->is_student_discount && $menu->student_price !== null)
                    ? $menu->student_price
                    : $menu->price;
                $subtotal = $unitPrice * (int) $item['quantity'];

                $orderItemsToInsert[] = [
                    'order_id'   => $order->id,
                    'menu_id'    => $menu->id,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal'   => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $total += $subtotal;
            }

            \App\Models\OrderItem::insert($orderItemsToInsert);

            $order->update(['total_amount' => $total]);

            return response()->json([
                'order_code'   => $order->order_code,
                'total_amount' => $order->total_amount,
                'order_id'     => $order->id,
            ], 201);
        }, 3);
    }
}
