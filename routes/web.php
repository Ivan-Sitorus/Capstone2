<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Cashier\CashierOrderController;
use App\Http\Controllers\Cashier\CashierPendingCountController;
use App\Http\Controllers\Cashier\CashierActiveOrdersController;
use App\Http\Controllers\Cashier\CashierNewOrderController;
use App\Http\Controllers\Cashier\CashierDashboardController;
use App\Http\Controllers\Cashier\CashierOrderHistoryController;
use App\Http\Controllers\Customer\CustomerMenuController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\CustomerPaymentController;
use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Cashier auth
Route::get('/kasir/login', [AuthController::class, 'showLogin'])->name('kasir.login');
Route::post('/kasir/login', [AuthController::class, 'login'])->name('kasir.login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Kasir pages
Route::prefix('kasir')->middleware(['auth:web', 'role:cashier'])->group(function () {
    Route::get('/pesanan-baru', [CashierNewOrderController::class, 'index'])->name('kasir.new-order');
    Route::post('/pesanan-baru', [CashierNewOrderController::class, 'store'])->name('kasir.new-order.store');
    Route::get('/pesanan-aktif', [CashierActiveOrdersController::class, 'index'])->name('kasir.active-orders');
    Route::get('/riwayat-pesanan', [CashierOrderHistoryController::class, 'index'])->name('kasir.order-history');
    Route::get('/pesanan/{order}', [CashierOrderController::class, 'show'])->name('kasir.order.show');
    Route::patch('/pesanan/{order}/status', [CashierOrderController::class, 'updateStatus'])->name('kasir.order.update-status');
    Route::patch('/pesanan/{order}/konfirmasi-tunai', [CashierOrderController::class, 'confirmCash'])->name('kasir.order.confirm-cash');
    Route::patch('/pesanan/{order}/konfirmasi-bayar', [CashierOrderController::class, 'confirmPayment'])->name('kasir.order.confirm-payment');
    Route::patch('/pesanan/{order}/konfirmasi-qris', [CashierOrderController::class, 'confirmQris'])->name('kasir.order.confirm-qris');
    Route::patch('/pesanan/{order}/tolak-qris', [CashierOrderController::class, 'rejectQris'])->name('kasir.order.reject-qris');
    Route::post('/pesanan/{order}/qris/accept', [CashierOrderController::class, 'acceptQrisProof'])->name('kasir.order.qris.accept');
    Route::post('/pesanan/{order}/qris/reject', [CashierOrderController::class, 'rejectQrisProof'])->name('kasir.order.qris.reject');
    Route::post('/pesanan/{order}/qris/resubmit', [CashierOrderController::class, 'requestQrisResubmit'])->name('kasir.order.qris.resubmit');
    Route::post('/pesanan/{order}/whatsapp-link', [CashierOrderController::class, 'whatsappLink'])->name('kasir.order.whatsapp-link');

    Route::get('/dashboard', [CashierDashboardController::class, 'index'])->name('kasir.dashboard');
    Route::patch('/pesanan/{order}/cancel', [CashierOrderController::class, 'cancel'])->name('kasir.order.cancel');
    Route::get('/profil', [CashierDashboardController::class, 'profile'])->name('kasir.profile');

    Route::get('/pesanan-menunggu', CashierPendingCountController::class)->name('kasir.pending-count');
});

// Customer pages (URL in Indonesian, route names in English)
Route::prefix('pelanggan')->group(function () {
    Route::get('/login', [AuthController::class, 'showCustomerLogin'])->name('customer.login');
    Route::post('/login', [AuthController::class, 'customerLogin'])->name('customer.login.attempt');

    Route::get('/menu', [CustomerMenuController::class, 'index'])->name('customer.menu');
    Route::get('/identitas', [CustomerMenuController::class, 'showIdentity'])->name('customer.identity');
    Route::post('/identitas', [CustomerMenuController::class, 'submitIdentity'])->name('customer.identity.submit');
    Route::get('/keranjang', fn () => Inertia::render('Customer/Cart/Index', []))->name('customer.cart');
    Route::get('/riwayat', [CustomerOrderController::class, 'history'])->name('customer.history');

    // Order flow
    Route::get('/pesanan/{order}/payment', [CustomerPaymentController::class, 'showChoose'])->name('customer.payment.choose');
    Route::post('/pesanan/{order}/payment/choose', [CustomerPaymentController::class, 'choose'])->name('customer.payment.choose.post');
    Route::get('/pesanan/{order}/payment/qris', [CustomerPaymentController::class, 'showQris'])->name('customer.payment.qris');
    Route::post('/pesanan/{order}/payment/qris', [CustomerPaymentController::class, 'uploadQris'])->name('customer.payment.qris.upload');
    Route::get('/pesanan/{order}/status', [CustomerOrderController::class, 'showStatus'])->name('customer.order.status');
});

// QR table entry — accepts {APP_URL}/order?table={token} and forwards to the identity form
Route::get('/order', fn () => redirect()->route('customer.identity', request()->only('table')))
    ->name('customer.order.entry');

// Receipt (public — no auth required, unguessable capability token)
Route::get('/struk-pesanan/{order:receipt_token}', [ReceiptController::class, 'showByReceiptToken'])->name('receipt.show-by-token');
