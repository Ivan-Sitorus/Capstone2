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
        $pesananAktif = $this->dashboardService->getActiveOrdersCount();
        $transaksiTerbaru = $this->dashboardService->getRecentTransactions();

        return Inertia::render('Kasir/Dashboard', [
            'totalPenjualan'   => (float) ($stats->total_penjualan ?? 0),
            'jumlahTransaksi'  => (int)   ($stats->jumlah_transaksi ?? 0),
            'pesananAktif'     => $pesananAktif,
            'cashPending'      => (int)   ($stats->cash_pending ?? 0),
            'qrisPending'      => (int)   ($stats->qris_pending ?? 0),
            'transaksiTerbaru' => $transaksiTerbaru,
        ]);
    }

    public function profil(): Response
    {
        return Inertia::render('Kasir/Profil', [
            'user' => auth()->user(),
        ]);
    }
}
