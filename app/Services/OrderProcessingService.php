<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\QrisStatus;
use App\Exceptions\InsufficientStockException;
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
            throw new InsufficientStockException("Stok '{$name}' tidak mencukupi.");
        }

        $updated = DB::transaction(function () use ($order) {
            // Conditional update: only the cashier whose read still matches the
            // stored status may move the order to processing. A concurrent
            // cashier gets 0 affected rows and is rejected by the caller.
            $affected = Order::whereKey($order->id)
                ->where('status', OrderStatus::Pending->value)
                ->update([
                    'status' => OrderStatus::Processing,
                    'cashier_id' => Auth::id(),
                    'processed_at' => now(),
                ]);

            if ($affected === 0) {
                return false;
            }

            $this->inventoryService->processSaleForOrder($order->refresh());

            return true;
        });

        if (! $updated) {
            throw new \RuntimeException('Pesanan sudah diproses oleh kasir lain.');
        }

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
        $proofPath = $order->payment_proof;

        DB::transaction(function () use ($order, $reason) {
            $affected = Order::whereKey($order->id)
                ->where('qris_status', QrisStatus::ProofSubmitted->value)
                ->update([
                    'qris_status' => QrisStatus::Rejected->value,
                    'payment_proof' => null,
                    'rejection_note' => $reason,
                ]);

            if ($affected === 0) {
                throw new \RuntimeException('Bukti QRIS sudah diproses oleh kasir lain.');
            }
        });

        if ($proofPath) {
            Storage::disk('public')->delete($proofPath);
        }
    }

    public function acceptQrisProof(Order $order): void
    {
        $proofPath = $order->payment_proof;

        DB::transaction(function () use ($order) {
            $affected = Order::whereKey($order->id)
                ->where('qris_status', QrisStatus::ProofSubmitted->value)
                ->update([
                    'qris_status' => QrisStatus::Accepted->value,
                    'status' => OrderStatus::Processing->value,
                    'cashier_id' => Auth::id(),
                    'payment_proof' => null,
                    'processed_at' => now(),
                ]);

            if ($affected === 0) {
                throw new \RuntimeException('Bukti QRIS sudah diproses oleh kasir lain.');
            }

            $this->inventoryService->processSaleForOrder($order->refresh());
        });

        if ($proofPath) {
            Storage::disk('public')->delete($proofPath);
        }
    }

    public function requestQrisResubmit(Order $order, ?string $reason): void
    {
        $proofPath = $order->payment_proof;

        DB::transaction(function () use ($order, $reason) {
            $affected = Order::whereKey($order->id)
                ->where('qris_status', QrisStatus::ProofSubmitted->value)
                ->update([
                    'qris_status' => QrisStatus::ResubmitRequested->value,
                    'payment_proof' => null,
                    'rejection_note' => $reason,
                ]);

            if ($affected === 0) {
                throw new \RuntimeException('Bukti QRIS sudah diproses oleh kasir lain.');
            }
        });

        if ($proofPath) {
            Storage::disk('public')->delete($proofPath);
        }
    }
}
