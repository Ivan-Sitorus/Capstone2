<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UploadQrisProofAction
{
    public function handle(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'proof' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($order->status !== OrderStatus::Pending) {
            return response()->json(['message' => 'Status pesanan tidak valid.'], 409);
        }

        if ($order->payment_proof) {
            Storage::disk('public')->delete($order->payment_proof);
        }

        $path = $request->file('proof')->store('proofs', 'public');

        DB::transaction(function () use ($order, $path) {
            $updates = [
                'payment_proof'  => $path,
                'payment_method' => 'qris',
                'rejection_note' => null,
            ];
            if (!$order->order_code) {
                $updates['order_code'] = Order::generateCode();
            }
            $order->update($updates);
        });

        return response()->json([
            'message'    => 'Bukti berhasil dikirim',
            'order_code' => $order->fresh()->order_code,
        ]);
    }
}
