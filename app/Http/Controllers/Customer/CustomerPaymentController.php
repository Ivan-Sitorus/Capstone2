<?php

namespace App\Http\Controllers\Customer;

use App\Actions\ChooseCashAction;
use App\Actions\ChooseQrisAction;
use App\Actions\UploadQrisProofAction;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CustomerPaymentController extends Controller
{
    public function showChoose(Order $order): Response
    {
        if ($order->status !== OrderStatus::Pending) {
            return Inertia::location('/pelanggan/riwayat');
        }

        $payload = Cache::remember("order_choose_{$order->id}", 120, function () use ($order) {
            $order->load(['items.menu', 'cafeTable']);
            return [
                'order'        => $order->only(['id', 'order_code', 'total_amount', 'customer_name']),
                'items'        => $order->items->map(fn($i) => [
                    'name'     => $i->menu->name,
                    'qty'      => $i->quantity,
                    'subtotal' => $i->subtotal,
                ])->values(),
                'table_number' => $order->cafeTable?->table_number,
            ];
        });

        return Inertia::render('Customer/Payment/Choose', $payload);
    }

    public function chooseCash(Request $request, Order $order): JsonResponse
    {
        return app(ChooseCashAction::class)->handle($order);
    }

    public function chooseQris(Request $request, Order $order): JsonResponse
    {
        return app(ChooseQrisAction::class)->handle($order);
    }

    public function showQrisUpload(Order $order): Response
    {
        if (in_array($order->status, [OrderStatus::Processing, OrderStatus::Completed])) {
            return Inertia::render('Customer/Payment/QrisStatus', ['order' => $this->orderData($order)]);
        }

        $rejectedMessage = ($order->payment_method === PaymentMethod::Qris && $order->rejection_note && !$order->payment_proof)
            ? $order->rejection_note
            : null;

        return Inertia::render('Customer/Payment/QrisUpload', [
            'order'           => $order->only(['id', 'order_code', 'total_amount']),
            'qrisImage'       => asset('storage/' . (Setting::get('qris_image') ?? '')),
            'qrisName'        => Setting::get('qris_name') ?? '',
            'totalAmount'     => $order->total_amount,
            'rejectedMessage' => $rejectedMessage,
        ]);
    }

    public function uploadQrisProof(Request $request, Order $order): JsonResponse
    {
        return app(UploadQrisProofAction::class)->handle($request, $order);
    }

    public function showQrisStatus(Order $order): Response
    {
        return Inertia::render('Customer/Payment/QrisStatus', ['order' => $this->orderData($order)]);
    }

    private function orderData(Order $order): array
    {
        return [
            'id'             => $order->id,
            'order_code'     => $order->order_code,
            'status'         => $order->status,
            'total_amount'   => $order->total_amount,
            'payment_method' => $order->payment_method,
            'rejection_note' => $order->rejection_note,
        ];
    }

    public function choose(Request $request, Order $order): JsonResponse
    {
        return $request->input('payment_method') === PaymentMethod::Qris->value
            ? $this->chooseQris($request, $order)
            : $this->chooseCash($request, $order);
    }

    public function showQris(Order $order): Response
    {
        return $this->showQrisUpload($order);
    }

    public function uploadQris(Request $request, Order $order): JsonResponse
    {
        return $this->uploadQrisProof($request, $order);
    }
}
