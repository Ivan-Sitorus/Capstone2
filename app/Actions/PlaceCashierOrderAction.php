<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\InventoryService;
use App\Services\OrderPromotionService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class PlaceCashierOrderAction
{
    public function __construct(
        protected OrderPromotionService $orderPromotionService,
        protected InventoryService $inventoryService,
    ) {}

    public function handle(StoreOrderRequest $request): array
    {
        $uuid = $request->uuid ?? (string) Uuid::uuid7();
        $orderModel = null;

        $attempt = function () use ($request, &$uuid, &$orderModel) {
            DB::transaction(function () use ($request, &$uuid, &$orderModel) {
                $selectedPromotionIds = $request->input('promotion_ids', []);

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
                $appliedPromotions = [];
                $itemsToInsert = [];

                $menuIds = collect($request->items)->pluck('menu_id')->unique()->all();
                $menus = Menu::whereIn('id', $menuIds)->get()->keyBy('id');

                foreach ($request->items as $item) {
                    $menu = $menus->get($item['menu_id']);

                    $lineCalculation = $this->orderPromotionService->calculateLine(
                        $menu,
                        (int) $item['quantity'],
                        $isMahasiswa,
                        $selectedPromotionIds,
                    );

                    $itemsToInsert[] = [
                        'order_id' => $order->id,
                        'menu_id' => $menu->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $lineCalculation['unit_price'],
                        'subtotal' => $lineCalculation['subtotal'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if ($lineCalculation['applied_promotion'] !== null) {
                        $appliedPromotions[] = $lineCalculation['applied_promotion'];
                    }

                    $total += $lineCalculation['subtotal'];
                }

                OrderItem::insert($itemsToInsert);

                $order->update(['total_amount' => $total]);
                $orderModel = $order;

                $this->orderPromotionService->persistOrderPromotions($order, $appliedPromotions);

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
