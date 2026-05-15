<?php

namespace App\Services;

use App\Models\IngredientBatch;
use App\Models\Order;
use App\Models\Receivable;
use App\Models\UnexpectedTransaction;
use Carbon\Carbon;

/**
 * @deprecated Use App\Services\FinancialReportService instead. Will be removed in next major version.
 */
class RigidReportService
{
    /**
     * Generate Income Statement + Cash Flow Statement for a date range.
     * Cash basis: only orders with is_paid = true are recognized.
     */
    public function generate(string $dateStart, string $dateEnd): array
    {
        $s = Carbon::parse($dateStart)->startOfDay();
        $e = Carbon::parse($dateEnd)->endOfDay();

        $pendapatanOrders = (float) Order::where('is_paid', true)
            ->whereBetween('created_at', [$s, $e])
            ->sum('total_amount');

        $pendapatanUnexpected = (float) UnexpectedTransaction::where('jenis', 'pemasukan')
            ->whereBetween('created_at', [$s, $e])
            ->sum('nominal');

        $pendapatan = $pendapatanOrders + $pendapatanUnexpected;

        $hpp = (float) IngredientBatch::whereBetween('received_at', [$s, $e])
            ->selectRaw('COALESCE(SUM(quantity * cost_per_unit), 0) as total')
            ->value('total');

        $labaKotor = $pendapatan - $hpp;

        $bebanOperasional = 0;

        $bebanTakTerduga = (float) UnexpectedTransaction::where('jenis', 'pengeluaran')
            ->whereBetween('created_at', [$s, $e])
            ->sum('nominal');

        $labaRugiBersih = $labaKotor - $bebanOperasional - $bebanTakTerduga;

        $receivablePayments = (float) Receivable::whereIn('status', [
            Receivable::STATUS_PAID,
            Receivable::STATUS_PARTIAL,
        ])
            ->whereBetween('updated_at', [$s, $e])
            ->sum('paid_amount');

        $arusKasMasuk = $pendapatan + $receivablePayments;
        $arusKasKeluar = $bebanOperasional + $hpp + $bebanTakTerduga;
        $arusKasBersih = $arusKasMasuk - $arusKasKeluar;

        return [
            'meta' => [
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
                'generated_at' => now()->toDateTimeString(),
                'type' => 'rigid',
            ],
            'income_statement' => [
                'pendapatan' => $pendapatan,
                'pendapatan_orders' => $pendapatanOrders,
                'pendapatan_unexpected' => $pendapatanUnexpected,
                'hpp' => $hpp,
                'laba_kotor' => $labaKotor,
                'beban_operasional' => $bebanOperasional,
                'beban_tak_terduga' => $bebanTakTerduga,
                'laba_rugi_bersih' => $labaRugiBersih,
            ],
            'cash_flow' => [
                'arus_kas_masuk' => $arusKasMasuk,
                'pendapatan' => $pendapatan,
                'receivable_payments' => $receivablePayments,
                'arus_kas_keluar' => $arusKasKeluar,
                'beban_operasional' => $bebanOperasional,
                'hpp' => $hpp,
                'beban_tak_terduga' => $bebanTakTerduga,
                'arus_kas_bersih' => $arusKasBersih,
                'saldo_awal' => 0,
                'saldo_akhir' => $arusKasBersih,
            ],
        ];
    }
}
