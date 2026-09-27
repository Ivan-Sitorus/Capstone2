<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\QrisStatus;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UploadQrisProofAction
{
    /** Batas jumlah pengunggahan ulang bukti QRIS. */
    public const MAX_RESUBMIT_ATTEMPTS = 3;

    public function handle(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'proof' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($order->status !== OrderStatus::Pending) {
            return response()->json(['message' => 'Status pesanan tidak valid.'], 409);
        }

        // A re-upload only counts as a "resubmit" when the cashier explicitly
        // asked for one. Enforce the cap before touching the stored proof so a
        // customer cannot loop forever after the limit is reached.
        $isResubmit = $order->qris_status === QrisStatus::ResubmitRequested;

        if ($isResubmit && $order->qris_resubmit_attempts >= self::MAX_RESUBMIT_ATTEMPTS) {
            return response()->json([
                'message' => 'Batas pengunggahan ulang bukti QRIS ('.self::MAX_RESUBMIT_ATTEMPTS.' kali) telah tercapai.',
            ], 409);
        }

        if ($order->payment_proof) {
            Storage::disk('public')->delete($order->payment_proof);
        }

        $path = $request->file('proof')->store('proofs', 'public');

        Order::retryOnCodeCollision(function () use ($order, $path, $isResubmit) {
            DB::transaction(function () use ($order, $path, $isResubmit) {
                $updates = [
                    'payment_proof'  => $path,
                    'payment_method' => PaymentMethod::Qris->value,
                    'rejection_note' => null,
                    'qris_status'    => QrisStatus::ProofSubmitted->value,
                ];

                if ($isResubmit) {
                    $updates['qris_resubmit_attempts'] = $order->qris_resubmit_attempts + 1;
                }

                if (!$order->order_code) {
                    $updates['order_code'] = Order::generateCode();
                }

                $order->update($updates);
            });
        });

        return response()->json([
            'message'    => 'Bukti berhasil dikirim',
            'order_code' => $order->fresh()->order_code,
        ]);
    }
}
