<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

class CustomerPaymentController extends Controller
{
    public function showChoose(string $orderCode): InertiaResponse|RedirectResponse
    {
        $order = Order::with(['items.menu', 'cafeTable'])
            ->where('order_code', $orderCode)
            ->firstOrFail();

        if ($order->status !== Order::STATUS_PENDING) {
            return Inertia::location('/customer/riwayat');
        }

        return Inertia::render('Pelanggan/Payment/Choose', [
            'order' => $order->only(['id', 'order_code', 'total_amount', 'customer_name']),
            'items' => $order->items->map(fn ($i) => [
                'name' => $i->menu->name,
                'qty' => $i->quantity,
                'subtotal' => $i->subtotal,
            ]),
            'table_number' => $order->cafeTable?->table_number,
            'qrisImage' => asset('storage/'.Setting::get('qris_image', 'qris/qris-w9cafe.png')),
        ]);
    }

    public function chooseCash(Order $order): JsonResponse
    {
        if ($order->status !== Order::STATUS_PENDING) {
            return response()->json(['message' => 'Status pesanan tidak valid.'], 409);
        }
        $order->update(['payment_method' => 'cash']);

        return response()->json(['message' => 'ok', 'order_code' => $order->order_code]);
    }

    public function chooseQris(Order $order): JsonResponse
    {
        if ($order->status !== Order::STATUS_PENDING) {
            return response()->json(['message' => 'Status pesanan tidak valid.'], 409);
        }
        $order->update(['payment_method' => 'qris']);

        return response()->json([
            'qris_image' => asset('storage/'.Setting::get('qris_image', 'qris/qris-w9cafe.png')),
            'qris_name' => Setting::get('qris_name', 'W9 Cafe'),
            'total_amount' => $order->total_amount,
            'order_code' => $order->order_code,
        ]);
    }

    public function showCashStatus(string $orderCode): RedirectResponse
    {
        return redirect('/customer/riwayat');
    }

    public function showQrisUpload(string $orderCode): InertiaResponse
    {
        $order = Order::where('order_code', $orderCode)->firstOrFail();

        if (in_array($order->status, [Order::STATUS_DIPROSES, Order::STATUS_SELESAI])) {
            return Inertia::render('Pelanggan/Payment/QrisStatus', ['order' => $this->orderData($order)]);
        }

        // rejection_note is set when cashier rejects and clears proof, allowing re-upload
        $rejectedMessage = ($order->payment_method === 'qris' && $order->rejection_note && ! $order->payment_proof)
            ? $order->rejection_note
            : null;

        return Inertia::render('Pelanggan/Payment/QrisUpload', [
            'order' => $order->only(['id', 'order_code', 'total_amount']),
            'qrisImage' => asset('storage/'.Setting::get('qris_image', 'qris/qris-w9cafe.png')),
            'qrisName' => Setting::get('qris_name', 'W9 Cafe'),
            'totalAmount' => $order->total_amount,
            'rejectedMessage' => $rejectedMessage,
            'resubmitCount' => $order->resubmit_count,
        ]);
    }

    public function uploadQrisProof(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'proof' => 'required|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($order->status !== Order::STATUS_PENDING) {
            return response()->json(['message' => 'Status pesanan tidak valid.'], 409);
        }

        // Resubmit tracking: increment on resubmit_requested, block if limit reached
        if ($order->qris_status === 'resubmit_requested') {
            $order->increment('resubmit_count');
            $order->refresh();

            if ($order->resubmit_count >= 3) {
                return response()->json([
                    'message' => 'Batas kirim ulang tercapai (maks 3x)',
                ], 422);
            }
        }

        // Delete old proof file
        if ($order->payment_proof) {
            Storage::disk('public')->delete($order->payment_proof);
        }

        // Compress to WebP (quality 70), fallback to JPEG
        $file = $request->file('proof');
        $manager = new ImageManager(new Driver);
            $image = $manager->decode($file);

        try {
            $encoded = $image->encodeUsingFormat(Format::WEBP, quality: 70);
            $extension = 'webp';
        } catch (\Exception) {
            $encoded = $image->encodeUsingFormat(Format::JPEG, quality: 70);
            $extension = 'jpg';
        }

        $binary = base64_decode($encoded->toBase64());
        $filename = 'qris_'.time().'_'.uniqid().'.'.$extension;
        $path = 'qris-proofs/'.$filename;

        Storage::disk('public')->put($path, $binary);

        $order->update([
            'payment_proof' => $path,
            'payment_method' => 'qris',
            'qris_status' => 'proof_submitted',
            'rejection_note' => null,
        ]);

        return response()->json(['message' => 'Bukti berhasil dikirim']);
    }

    public function showQrisStatus(string $orderCode): InertiaResponse
    {
        $order = Order::where('order_code', $orderCode)->firstOrFail();

        return Inertia::render('Pelanggan/Payment/QrisStatus', ['order' => $this->orderData($order)]);
    }

    private function orderData(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_code' => $order->order_code,
            'status' => $order->status,
            'total_amount' => $order->total_amount,
            'payment_method' => $order->payment_method,
            'rejection_note' => $order->rejection_note,
            'qris_status' => $order->qris_status,
            'resubmit_count' => $order->resubmit_count,
        ];
    }
}
