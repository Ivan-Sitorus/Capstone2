<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\InventoryService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class PlaceCashierOrderAction
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    public function handle(StoreOrderRequest $request): array
    {
        $uuid = $request->uuid ?? (string) Uuid::uuid7();
        $orderModel = null;

        $attempt = function () use ($request, &$uuid, &$orderModel) {
            DB::transaction(function () use ($request, &$uuid, &$orderModel) {
                $order = Order::create([
                    'uuid' => $uuid,
                    'cashier_id' => Auth::id(),
                    'order_type' => 'cashier',
                    'payment_method' => $request->payment_method,
                    'customer_name' => $request->customer_name,
                    'status' => OrderStatus::Pending->value,
                    'total_amount' => 0,
                ]);

                $isMahasiswa = (bool) $request->input('is_mahasiswa', false);
                $total = 0;
                $itemsToInsert = [];

                $menuIds = collect($request->items)->pluck('menu_id')->unique()->all();
                $menus = Menu::whereIn('id', $menuIds)->get()->keyBy('id');

                foreach ($request->items as $item) {
                    $menu = $menus->get($item['menu_id']);

                    $unitPrice = ($isMahasiswa && $menu->is_student_discount && $menu->student_price !== null)
                        ? $menu->student_price
                        : $menu->price;
                    $subtotal = $unitPrice * (int) $item['quantity'];

                    $itemsToInsert[] = [
                        'order_id' => $order->id,
                        'menu_id' => $menu->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $total += $subtotal;
                }

                OrderItem::insert($itemsToInsert);

                $order->update(['total_amount' => $total]);
                $orderModel = $order;

                $this->inventoryService->processSaleForOrder($order, Auth::id());
            });
        };

        try {
            $attempt();
        } catch (UniqueConstraintViolationException) {
            $uuid = (string) Uuid::uuid7();
            $attempt();
        }

        return [
            'order_id' => $orderModel?->id,
            'order_total' => $orderModel?->total_amount,
            'order_code' => $orderModel?->order_code,
        ];
    }
}
