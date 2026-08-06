<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\QrisStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderProcessingService
{
    public function __construct(
        protected InventoryService $inventoryService,
    ) {}

    public function processOrder(Order $order, PaymentMethod $method): array
    {
        if ($order->status !== OrderStatus::Pending) {
            throw new \RuntimeException('Status pesanan tidak valid.');
        }
        if ($order->payment_method !== $method) {
            throw new \RuntimeException('Metode pembayaran tidak sesuai.');
        }

        $order->load('items.menu');
        $items = $order->items->map(fn($i) => ['menu_id' => $i->menu_id, 'quantity' => $i->quantity])->toArray();
        $fulfillment = $this->inventoryService->canFulfillOrder($items);
        if (!$fulfillment['can_fulfill']) {
            $first = $fulfillment['insufficient_ingredients'][0];
            $name = $first['ingredient_name'] ?? $first['menu_name'] ?? 'item';
            throw new \RuntimeException("Stok '{$name}' tidak mencukupi.");
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => OrderStatus::Processing,
                'cashier_id' => Auth::id(),
                'processed_at' => now(),
            ]);
            $this->inventoryService->processSaleForOrder($order);
        });

        return ['message' => 'Pesanan diproses.'];
    }

    public function confirmCash(Order $order): array
    {
        return $this->processOrder($order, PaymentMethod::Cash);
    }

    public function confirmQris(Order $order): array
    {
        return $this->processOrder($order, PaymentMethod::Qris);
    }

    public function rejectQrisProof(Order $order, ?string $reason): void
    {
        DB::transaction(function () use ($order, $reason) {
            if ($order->payment_proof) {
                Storage::disk('public')->delete($order->payment_proof);
            }
            $order->update([
                'qris_status' => QrisStatus::Rejected,
                'payment_proof' => null,
                'rejection_note' => $reason,
            ]);
        });
    }

    public function acceptQrisProof(Order $order): void
    {
        DB::transaction(function () use ($order) {
            if ($order->payment_proof) {
                Storage::disk('public')->delete($order->payment_proof);
            }
            $order->update([
                'qris_status' => QrisStatus::Accepted,
                'status' => OrderStatus::Processing,
                'cashier_id' => Auth::id(),
                'payment_proof' => null,
                'processed_at' => now(),
            ]);
            $this->inventoryService->processSaleForOrder($order);
        });
    }

    public function requestQrisResubmit(Order $order, ?string $reason): void
    {
        DB::transaction(function () use ($order, $reason) {
            if ($order->payment_proof) {
                Storage::disk('public')->delete($order->payment_proof);
            }
            $order->update([
                'qris_status' => QrisStatus::ResubmitRequested,
                'payment_proof' => null,
                'rejection_note' => $reason,
            ]);
        });
    }
}
