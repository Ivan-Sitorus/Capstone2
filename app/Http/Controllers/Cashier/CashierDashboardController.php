<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class CashierDashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(): Response
    {
        $stats = $this->dashboardService->getTodayStats();
        $activeOrders = $this->dashboardService->getActiveOrdersCount();
        $recentTransactions = $this->dashboardService->getRecentTransactions();

        return Inertia::render('Cashier/Dashboard', [
            'totalSales'        => (float) ($stats->total_penjualan ?? 0),
            'transactionCount'  => (int)   ($stats->jumlah_transaksi ?? 0),
            'activeOrders'      => $activeOrders,
            'cashPending'       => (int)   ($stats->cash_pending ?? 0),
            'qrisPending'       => (int)   ($stats->qris_pending ?? 0),
            'recentTransactions' => $recentTransactions,
        ]);
    }

    public function profile(): Response
    {
        return Inertia::render('Cashier/Profile', [
            'user' => auth()->user(),
        ]);
    }
}
