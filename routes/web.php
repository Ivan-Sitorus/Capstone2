<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Customer\CustomerMenuController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Controllers\Customer\CustomerPaymentController;
use App\Http\Controllers\Cashier\CashierOrderController;
use App\Http\Controllers\Cashier\CashierPesananAktifController;
use App\Http\Controllers\Cashier\CashierPesananBaruController;
use App\Http\Controllers\Cashier\CashierRiwayatController;
use App\Http\Controllers\Kitchen\KitchenController;
use App\Http\Controllers\ReceiptController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn() => redirect()->route('cashier.login'));

// Cashier auth
Route::get('/cashier/login',  [AuthController::class, 'showLogin'])->name('cashier.login');
Route::post('/cashier/login', [AuthController::class, 'login'])->name('cashier.login.attempt');
// Legacy redirect + named route for auth middleware fallback
Route::get('/login',  fn() => redirect()->route('cashier.login'))->name('login');
Route::post('/login', fn() => redirect()->route('cashier.login'));

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Kasir pages
Route::prefix('cashier')->middleware(['auth', 'role:cashier,admin'])->group(function () {
    Route::get('/pesanan-baru',  [CashierPesananBaruController::class, 'index'])->name('cashier.pesanan-baru');
    Route::post('/pesanan-baru', [CashierPesananBaruController::class, 'store'])->name('cashier.pesanan-baru.store');
    Route::get('/pesanan-aktif', [CashierPesananAktifController::class, 'index'])->name('cashier.pesanan-aktif');
    Route::get('/riwayat',       [CashierRiwayatController::class, 'index'])->name('cashier.riwayat');
    Route::get('/order/{order}',              [CashierOrderController::class, 'show'])->name('cashier.order.show');
    Route::patch('/order/{order}/status',     [CashierOrderController::class, 'updateStatus'])->name('cashier.order.status');
    Route::patch('/order/{order}/confirm-cash',    [CashierOrderController::class, 'confirmCash'])->name('cashier.order.confirm-cash');
    Route::patch('/order/{order}/confirm-payment', [CashierOrderController::class, 'confirmPayment'])->name('cashier.order.confirm-payment');
    Route::patch('/order/{order}/confirm-qris', [CashierOrderController::class, 'confirmQris'])->name('cashier.order.confirm-qris');
    Route::patch('/order/{order}/reject-qris',  [CashierOrderController::class, 'rejectQris'])->name('cashier.order.reject-qris');

    Route::get('/pending-count', \App\Http\Controllers\Cashier\CashierPendingCountController::class)->name('cashier.pending-count');
});

// Kitchen auth
Route::get('/kitchen/login', [AuthController::class, 'showKitchenLogin'])->name('kitchen.login');
Route::post('/kitchen/login', [AuthController::class, 'login'])->name('kitchen.login.attempt');

// Kitchen
Route::middleware(['auth', 'role:kitchen,admin'])->group(function () {
    Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
    Route::get('/kitchen/riwayat', [KitchenController::class, 'riwayat'])->name('kitchen.riwayat');
    Route::patch('/kitchen/order/{order}/bump', [KitchenController::class, 'bump'])->name('kitchen.bump');
});

// Customer — entry point via QR scan
Route::get('/order', [CustomerMenuController::class, 'showIdentitas'])->name('customer.identitas');

// Customer pages
Route::prefix('customer')->group(function () {
    Route::get('/menu',    [CustomerMenuController::class, 'index'])->name('customer.menu');
    Route::get('/cart',    fn() => Inertia::render('Customer/Cart/Index', []))->name('customer.cart');
    Route::get('/order/{code}/status', [CustomerOrderController::class, 'status'])->name('customer.order.status');

    // Payment flow
    Route::get('/payment/{orderCode}/choose',      [CustomerPaymentController::class, 'showChoose'])->name('customer.payment.choose');
    Route::get('/payment/{orderCode}/cash-status', [CustomerPaymentController::class, 'showCashStatus'])->name('customer.payment.cash-status');
    Route::get('/payment/{orderCode}/qris',        [CustomerPaymentController::class, 'showQrisUpload'])->name('customer.payment.qris-upload');
    Route::get('/payment/{orderCode}/qris-status', [CustomerPaymentController::class, 'showQrisStatus'])->name('customer.payment.qris-status');
});

// Receipt (public — no auth required)
Route::get('/receipt/{order:code}', [ReceiptController::class, 'show'])->name('receipt.show');
