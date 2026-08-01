<?php

namespace App\Observers;

use App\Models\BatchPayment;

class BatchPaymentObserver
{
    public function created(BatchPayment $payment): void
    {
        $payment->batch->recalculatePaymentStatus();
    }

    public function updated(BatchPayment $payment): void
    {
        $payment->batch->recalculatePaymentStatus();
    }

    public function deleted(BatchPayment $payment): void
    {
        $payment->batch->load('batchPayments');
        $payment->batch->recalculatePaymentStatus();
    }
}
