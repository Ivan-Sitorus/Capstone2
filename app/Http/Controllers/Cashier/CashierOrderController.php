<?php

namespace App\Http\Controllers\Cashier;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\QrisStatus;
use App\Exceptions\InsufficientStockException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\InventoryService;
use App\Services\OrderProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CashierOrderController extends Controller
{
    public function __construct(
        protected OrderProcessingService $orderProcessingService
    ) {}

    public function show(Order $order): Response
    {
        $order->load(['items.menu', 'cafeTable', 'cashier']);

        return Inertia::render('Cashier/Order/Show', [
            'order' => [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'customer_name' => $order->customer_name,
                'phone' => $order->phone,
                'payment_method' => $order->payment_method,
                'payment_proof' => $order->payment_proof,
                'rejection_note' => $order->rejection_note,
                'created_at' => $order->created_at->toISOString(),
                'cashier_name' => $order->cashier?->name,
                'table_number' => $order->cafeTable?->table_number,
                'items' => $order->items->map(fn ($i) => [
                    'id' => $i->id,
                    'name' => $i->menu?->name,
                    'unit_price' => $i->unit_price,
                    'quantity' => $i->quantity,
                    'subtotal' => $i->subtotal,
                ]),
            ],
        ]);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        if (in_array($order->status, [OrderStatus::Completed, OrderStatus::Cancelled])) {
            return response()->json(['message' => 'Pesanan ini tidak dapat dibatalkan.'], 409);
        }

        $request->validate(['reason' => 'nullable|string|max:255']);

        // Conditional update keyed on the status we just read: if another
        // cashier processed the order in between, this affects 0 rows and we
        // refuse to cancel an order that is already being prepared.
        $affected = Order::whereKey($order->id)
            ->where('status', $order->status->value)
            ->update([
                'status'         => OrderStatus::Cancelled->value,
                'rejection_note' => $request->reason,
                'cashier_id'     => Auth::id(),
                'cancelled_at'   => now(),
            ]);

        if ($affected === 0) {
            return response()->json(['message' => 'Pesanan sudah diproses oleh kasir lain.'], 409);
        }

        return response()->json(['message' => 'Pesanan dibatalkan.']);
    }

    public function updateStatus(Request $request, Order $order, InventoryService $inventoryService): JsonResponse
    {
        $request->validate(['status' => 'required|string|in:processing,completed']);

        $currentStatus = $order->status->value;

        $validTransitions = [
            OrderStatus::Pending->value => OrderStatus::Processing->value,
            OrderStatus::Processing->value => OrderStatus::Completed->value,
        ];

        $allowed = $validTransitions[$currentStatus] ?? null;
        if (! $allowed || $allowed !== $request->status) {
            return response()->json(['message' => 'Transisi status tidak valid.'], 409);
        }

        if ($request->status === OrderStatus::Completed->value && $order->payment_method === PaymentMethod::PayLater) {
            return response()->json(['message' => 'Pesanan belum lunas. Konfirmasi pembayaran terlebih dahulu.'], 409);
        }

        if ($request->status === OrderStatus::Processing->value) {
            $order->load('items.menu');
            $items = $order->items->map(fn($i) => ['menu_id' => $i->menu_id, 'quantity' => $i->quantity])->toArray();
            $fulfillment = $inventoryService->canFulfillOrder($items);

            if (! $fulfillment['can_fulfill']) {
                $first = $fulfillment['insufficient_ingredients'][0];
                $name = $first['ingredient_name'] ?? $first['menu_name'] ?? 'item';
                return response()->json(['message' => "Stok '{$name}' tidak mencukupi."], 409);
            }
        }

        try {
            $updated = DB::transaction(function () use ($request, $order, $inventoryService, $currentStatus) {
                $data = ['status' => $request->status, 'cashier_id' => Auth::id()];

                if ($request->status === OrderStatus::Processing->value) {
                    $data['processed_at'] = now();
                } elseif ($request->status === OrderStatus::Completed->value) {
                    $data['completed_at'] = now();
                }

                // Guarded transition: only update when the stored status still
                // equals the one this cashier read. A second cashier racing the
                // same pending order gets 0 rows and is rejected below.
                $affected = Order::whereKey($order->id)
                    ->where('status', $currentStatus)
                    ->update($data);

                if ($affected === 0) {
                    return false;
                }

                if ($request->status === OrderStatus::Processing->value) {
                    $inventoryService->processSaleForOrder($order->refresh());
                }

                return true;
            });
        } catch (InsufficientStockException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => 'Gagal memproses pesanan: '.$e->getMessage()], 500);
        }

        if (! $updated) {
            return response()->json(['message' => 'Pesanan sudah diproses oleh kasir lain.'], 409);
        }

        return response()->json(['message' => 'Status diperbarui.']);
    }

    public function confirmPayment(Request $request, Order $order): JsonResponse
    {
        if ($order->payment_method !== PaymentMethod::PayLater) {
            return response()->json(['message' => 'Sudah lunas.'], 409);
        }
        $request->validate(['payment_method' => 'required|in:cash,qris']);

        $order->update([
            'payment_method' => $request->payment_method,
            'cashier_id' => Auth::id(),
        ]);
        

        return response()->json(['message' => 'Pembayaran dikonfirmasi.']);
    }

    public function confirmCash(Order $order): JsonResponse|RedirectResponse
    {
        if ($order->status !== OrderStatus::Pending || $order->payment_method !== PaymentMethod::Cash) {
            return response()->json(['message' => 'Status pesanan tidak valid.'], 409);
        }

        try {
            return response()->json($this->orderProcessingService->confirmCash($order));
        } catch (InsufficientStockException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function confirmQris(Order $order): JsonResponse|RedirectResponse
    {
        if ($order->status !== OrderStatus::Pending || $order->payment_method !== PaymentMethod::Qris) {
            return response()->json(['message' => 'Status pesanan tidak valid.'], 409);
        }

        try {
            return response()->json($this->orderProcessingService->confirmQris($order));
        } catch (InsufficientStockException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectQris(Request $request, Order $order): JsonResponse
    {
        if ($order->status !== OrderStatus::Pending || $order->payment_method !== PaymentMethod::Qris) {
            return response()->json(['message' => 'Status pesanan tidak valid.'], 409);
        }
        $request->validate(['note' => 'nullable|string|max:255']);

        if ($order->payment_proof) {
            Storage::disk('public')->delete($order->payment_proof);
        }

        $order->update([
            'payment_proof' => null,
            'rejection_note' => $request->note,
        ]);

        return response()->json(['message' => 'Bukti QRIS ditolak.']);
    }

    public function acceptQrisProof(Order $order): JsonResponse|RedirectResponse
    {
        if ($order->qris_status !== QrisStatus::ProofSubmitted) {
            return response()->json(['message' => 'Bukti QRIS tidak dalam status review.'], 409);
        }

        try {
            $this->orderProcessingService->acceptQrisProof($order);
            return response()->json(['message' => 'Bukti QRIS diterima. Pesanan diproses.']);
        } catch (InsufficientStockException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectQrisProof(Request $request, Order $order): JsonResponse
    {
        if ($order->qris_status !== QrisStatus::ProofSubmitted) {
            return response()->json(['message' => 'Bukti QRIS tidak dalam status review.'], 409);
        }

        $request->validate(['reason' => 'required|string|max:500']);

        try {
            $this->orderProcessingService->rejectQrisProof($order, $request->reason);
            return response()->json(['message' => 'Bukti QRIS ditolak.']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function requestQrisResubmit(Request $request, Order $order): JsonResponse
    {
        if ($order->qris_status !== QrisStatus::ProofSubmitted) {
            return response()->json(['message' => 'Bukti QRIS tidak dalam status review.'], 409);
        }

        $request->validate(['reason' => 'required|string|max:500']);

        try {
            $this->orderProcessingService->requestQrisResubmit($order, $request->reason);
            return response()->json(['message' => 'Pengunggahan ulang bukti QRIS diminta.']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function whatsappLink(Request $request, Order $order, WhatsAppReceiptService $waService): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        try {
            $waLink = $waService->buildWaMeLink($order, $request->phone);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['wa_link' => $waLink]);
    }
}
