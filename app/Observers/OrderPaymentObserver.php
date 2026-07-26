<?php

namespace App\Observers;

use App\Models\OrderPayment;

class OrderPaymentObserver
{
    public function created(OrderPayment $payment): void
    {
        $payment->order->recalculatePaymentStatus();
    }

    public function updated(OrderPayment $payment): void
    {
        $payment->order->recalculatePaymentStatus();
    }

    public function deleted(OrderPayment $payment): void
    {
        $payment->order->load('orderPayments');
        $payment->order->recalculatePaymentStatus();
    }
}
