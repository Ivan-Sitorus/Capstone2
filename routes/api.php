<?php

use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\CustomerPaymentController;
use Illuminate\Support\Facades\Route;

// Customer order store (called via axios from Cart page). `customer.session`
// starts the session on these API routes so the order can bind the anonymous
// customer identity for later ownership checks.
Route::post('/pesanan', [CustomerOrderController::class, 'store'])
    ->middleware('customer.session')
    ->name('customer.order.store');

// Payment method selection. Every route that resolves an order id must prove
// the order belongs to the session-bound customer identity.
Route::middleware(['customer.session', 'customer.order'])->group(function () {
    Route::post('/pesanan/{order}/pay/cash', [CustomerPaymentController::class, 'chooseCash'])->name('customer.payment.cash');
    Route::post('/pesanan/{order}/pay/qris', [CustomerPaymentController::class, 'chooseQris'])->name('customer.payment.qris-init');
    Route::post('/pesanan/{order}/qris-proof', [CustomerPaymentController::class, 'uploadQrisProof'])->name('customer.payment.qris-proof');
});
