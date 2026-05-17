<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Jobs\BroadcastPendingCount;
use App\Models\CafeTable;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderPromotionService;
use Exception;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Ramsey\Uuid\Uuid;

class CustomerOrderController extends Controller
{
    public function store(Request $request, OrderPromotionService $orderPromotionService): JsonResponse
    {
        $request->validate([
            'uuid' => 'nullable|uuid',
            'customer_name' => 'nullable|string|min:2|max:255',
            'customer_phone' => ['nullable', 'string', 'regex:/^[0-9]{10,15}$/'],
            'table_id' => 'required|integer|exists:cafe_tables,id',
            'is_mahasiswa' => 'boolean',
            'promotion_ids' => 'nullable|array',
            'promotion_ids.*' => 'integer|exists:promotions,id',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|integer|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1|max:20',
        ], [
            'customer_phone.regex' => 'Nomor telepon tidak valid.',
            'items.required' => 'Pesanan tidak boleh kosong.',
            'items.min' => 'Minimal 1 item dalam pesanan.',
        ]);

        $uuid = $request->input('uuid') ?: (string) Uuid::uuid7();

        $attempt = function () use ($request, $orderPromotionService, &$uuid) {
            return DB::transaction(function () use ($request, $orderPromotionService, &$uuid) {
                CafeTable::findOrFail($request->table_id);

                $isMahasiswa = (bool) $request->input('is_mahasiswa', false);
                $selectedPromotionIds = $request->input('promotion_ids', []);

                $order = Order::create([
                    'uuid' => $uuid,
                    'customer_name' => $request->customer_name,
                    'customer_phone' => $request->customer_phone,
                    'table_id' => $request->table_id,
                    'cashier_id' => null,
                    'order_type' => 'qr',
                    'status' => Order::STATUS_PENDING,
                    'total_amount' => 0,
                ]);

                $total = 0;
                $appliedPromotions = [];
                $orderItemsToInsert = [];

                // Bulk-fetch semua menu sekaligus — hindari N+1 query
                $menuIds = collect($request->items)->pluck('menu_id')->unique()->all();
                $menus = Menu::whereIn('id', $menuIds)->get()->keyBy('id');

                foreach ($request->items as $item) {
                    $menu = $menus->get($item['menu_id']);

                    if (! $menu || ! $menu->is_available) {
                        throw new Exception('Menu '.($menu?->name ?? "#{$item['menu_id']}").' tidak tersedia.');
                    }

                    $lineCalculation = $orderPromotionService->calculateLine(
                        $menu,
                        (int) $item['quantity'],
                        $isMahasiswa,
                        $selectedPromotionIds,
                    );

                    $orderItemsToInsert[] = [
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

                // Bulk insert semua order items sekaligus
                OrderItem::insert($orderItemsToInsert);

                $order->update(['total_amount' => $total]);

                $orderPromotionService->persistOrderPromotions($order, $appliedPromotions);

                // Broadcast ke kasir via queue — tidak memblokir response
                BroadcastPendingCount::dispatch()->afterCommit();

                return response()->json([
                    'order_code' => $order->order_code,
                    'total_amount' => $order->total_amount,
                    'order_id' => $order->id,
                ], 201);
            });
        };

        try {
            return $attempt();
        } catch (UniqueConstraintViolationException) {
            $uuid = (string) Uuid::uuid7();
            return $attempt();
        }
    }

    public function status(string $code)
    {
        $order = Order::select(['id', 'order_code', 'status', 'total_amount', 'payment_method', 'created_at'])
            ->where('order_code', $code)
            ->firstOrFail();

        return Inertia::render('Pelanggan/Order/Status', [
            'order' => [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'payment_method' => $order->payment_method,
                'created_at' => $order->created_at->toISOString(),
            ],
        ]);
    }
}
