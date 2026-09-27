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
        $user = auth()->user();

        // Pass only the fields the profile page renders; the raw user model
        // would otherwise expose status, verification and token metadata.
        return Inertia::render('Cashier/Profile', [
            'user' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'role'       => $user->role,
                'created_at' => $user->created_at,
            ],
        ]);
    }
}
