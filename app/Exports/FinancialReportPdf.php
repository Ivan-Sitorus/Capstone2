<?php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class FinancialReportPdf
{
    /**
     * Generate a PDF report for the given data, type, and date range.
     *
     * @param array $data Report data from SimpleReportService, RigidReportService, or CustomReportService
     * @param string $type Report type: 'simple', 'rigid', or 'custom'
     * @param string|null $dateStart Start date for the report
     * @param string|null $dateEnd End date for the report
     * @return \Barryvdh\DomPDF\PDF
     */
    public static function generate(array $data, string $type, ?string $dateStart = null, ?string $dateEnd = null)
    {
        // Format date range for display
        $startDate = $dateStart ? Carbon::parse($dateStart)->format('d M Y') : '-';
        $endDate = $dateEnd ? Carbon::parse($dateEnd)->format('d M Y') : '-';
        $dateRange = "{$startDate} - {$endDate}";

        // Determine report title based on type
        $reportTitles = [
            'simple' => 'Laporan Keuangan Sederhana',
            'rigid' => 'Laporan Keuangan Rigid (Income Statement & Cash Flow)',
            'custom' => 'Laporan Keuangan Kustom',
        ];
        $reportTitle = $reportTitles[$type] ?? 'Laporan Keuangan';

        // Prepare data based on report type
        $viewData = self::prepareViewData($data, $type, $reportTitle, $dateRange);

        // Generate PDF using dompdf
        $pdf = Pdf::loadView('exports.financial-report', $viewData);

        // Set PDF options for proper rendering
        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }

    /**
     * Download the PDF report.
     *
     * @param array $data Report data
     * @param string $type Report type
     * @param string|null $dateStart
     * @param string|null $dateEnd
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public static function download(array $data, string $type, ?string $dateStart = null, ?string $dateEnd = null)
    {
        $pdf = self::generate($data, $type, $dateStart, $dateEnd);

        $filename = self::generateFilename($type, $dateStart, $dateEnd);

        return $pdf->download($filename);
    }

    /**
     * Stream the PDF to browser.
     *
     * @param array $data Report data
     * @param string $type Report type
     * @param string|null $dateStart
     * @param string|null $dateEnd
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public static function stream(array $data, string $type, ?string $dateStart = null, ?string $dateEnd = null)
    {
        $pdf = self::generate($data, $type, $dateStart, $dateEnd);

        $filename = self::generateFilename($type, $dateStart, $dateEnd);

        return $pdf->stream($filename);
    }

    /**
     * Generate filename for the PDF.
     */
    private static function generateFilename(string $type, ?string $dateStart, ?string $dateEnd): string
    {
        $dateStr = '';
        if ($dateStart && $dateEnd) {
            $dateStr = '_' . Carbon::parse($dateStart)->format('Y-m-d') . '_to_' . Carbon::parse($dateEnd)->format('Y-m-d');
        }

        return "laporan_keuangan_{$type}{$dateStr}.pdf";
    }

    /**
     * Prepare data for the view based on report type.
     */
    private static function prepareViewData(array $data, string $type, string $reportTitle, string $dateRange): array
    {
        $baseData = [
            'report_title' => $reportTitle,
            'date_range' => $dateRange,
            'generated_at' => Carbon::now()->format('d M Y, H:i:s'),
            'type' => $type,
        ];

        switch ($type) {
            case 'simple':
                return self::prepareSimpleReportData($data, $baseData);

            case 'rigid':
                return self::prepareRigidReportData($data, $baseData);

            case 'custom':
                return self::prepareCustomReportData($data, $baseData);

            default:
                return $baseData;
        }
    }

    /**
     * Prepare data for Simple report.
     */
    private static function prepareSimpleReportData(array $data, array $baseData): array
    {
        // Format currency for display
        $formatRupiah = function ($amount) {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        };

        // Process income breakdown
        $incomeBreakdown = collect($data['income_breakdown'] ?? [])->map(function ($item) use ($formatRupiah) {
            $sourceLabels = [
                'cash' => 'Tunai',
                'qris' => 'QRIS',
                'transfer' => 'Transfer',
                'unexpected_income' => 'Pemasukan Tak Terduga',
            ];

            return [
                'source' => $sourceLabels[$item['source']] ?? ucfirst($item['source']),
                'total' => $formatRupiah($item['total']),
                'total_raw' => $item['total'],
                'count' => $item['count'],
            ];
        })->toArray();

        // Process expense breakdown
        $expenseBreakdown = collect($data['expense_breakdown'] ?? [])->map(function ($item) use ($formatRupiah) {
            $sourceLabels = [
                'ingredient_purchase' => 'Pembelian Bahan Baku',
                'unexpected_expense' => 'Pengeluaran Tak Terduga',
            ];

            return [
                'source' => $sourceLabels[$item['source']] ?? ucfirst($item['source']),
                'total' => $formatRupiah($item['total']),
                'total_raw' => $item['total'],
                'count' => $item['count'],
            ];
        })->toArray();

        return array_merge($baseData, [
            'total_income' => $formatRupiah($data['total_income'] ?? 0),
            'total_income_raw' => $data['total_income'] ?? 0,
            'total_expense' => $formatRupiah($data['total_expense'] ?? 0),
            'total_expense_raw' => $data['total_expense'] ?? 0,
            'net' => $formatRupiah($data['net'] ?? 0),
            'net_raw' => $data['net'] ?? 0,
            'net_positive' => ($data['net'] ?? 0) >= 0,
            'income_breakdown' => $incomeBreakdown,
            'expense_breakdown' => $expenseBreakdown,
            'receivables_outstanding' => $formatRupiah($data['receivables_outstanding'] ?? 0),
            'receivables_outstanding_raw' => $data['receivables_outstanding'] ?? 0,
        ]);
    }

    /**
     * Prepare data for Rigid report.
     */
    private static function prepareRigidReportData(array $data, array $baseData): array
    {
        $formatRupiah = function ($amount) {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        };

        $incomeStatement = $data['income_statement'] ?? [];
        $cashFlow = $data['cash_flow'] ?? [];

        return array_merge($baseData, [
            // Income Statement
            'pendapatan' => $formatRupiah($incomeStatement['pendapatan'] ?? 0),
            'pendapatan_raw' => $incomeStatement['pendapatan'] ?? 0,
            'pendapatan_orders' => $formatRupiah($incomeStatement['pendapatan_orders'] ?? 0),
            'pendapatan_unexpected' => $formatRupiah($incomeStatement['pendapatan_unexpected'] ?? 0),
            'hpp' => $formatRupiah($incomeStatement['hpp'] ?? 0),
            'hpp_raw' => $incomeStatement['hpp'] ?? 0,
            'laba_kotor' => $formatRupiah($incomeStatement['laba_kotor'] ?? 0),
            'laba_kotor_raw' => $incomeStatement['laba_kotor'] ?? 0,
            'beban_operasional' => $formatRupiah($incomeStatement['beban_operasional'] ?? 0),
            'beban_operasional_raw' => $incomeStatement['beban_operasional'] ?? 0,
            'beban_tak_terduga' => $formatRupiah($incomeStatement['beban_tak_terduga'] ?? 0),
            'beban_tak_terduga_raw' => $incomeStatement['beban_tak_terduga'] ?? 0,
            'laba_rugi_bersih' => $formatRupiah($incomeStatement['laba_rugi_bersih'] ?? 0),
            'laba_rugi_bersih_raw' => $incomeStatement['laba_rugi_bersih'] ?? 0,
            'laba_positive' => ($incomeStatement['laba_rugi_bersih'] ?? 0) >= 0,

            // Cash Flow
            'arus_kas_masuk' => $formatRupiah($cashFlow['arus_kas_masuk'] ?? 0),
            'arus_kas_masuk_raw' => $cashFlow['arus_kas_masuk'] ?? 0,
            'receivable_payments' => $formatRupiah($cashFlow['receivable_payments'] ?? 0),
            'arus_kas_keluar' => $formatRupiah($cashFlow['arus_kas_keluar'] ?? 0),
            'arus_kas_keluar_raw' => $cashFlow['arus_kas_keluar'] ?? 0,
            'arus_kas_bersih' => $formatRupiah($cashFlow['arus_kas_bersih'] ?? 0),
            'arus_kas_bersih_raw' => $cashFlow['arus_kas_bersih'] ?? 0,
            'arus_kas_positive' => ($cashFlow['arus_kas_bersih'] ?? 0) >= 0,
            'saldo_awal' => $formatRupiah($cashFlow['saldo_awal'] ?? 0),
            'saldo_akhir' => $formatRupiah($cashFlow['saldo_akhir'] ?? 0),
        ]);
    }

    /**
     * Prepare data for Custom report.
     */
    private static function prepareCustomReportData(array $data, array $baseData): array
    {
        $formatRupiah = function ($amount) {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        };

        // Process rows
        $rows = collect($data['rows'] ?? [])->map(function ($row) use ($formatRupiah) {
            return [
                'date' => Carbon::parse($row['date'])->format('d M Y'),
                'date_raw' => $row['date'],
                'category' => $row['category'],
                'type' => $row['type'],
                'type_label' => $row['type'] === 'Income' ? 'Pemasukan' : 'Pengeluaran',
                'amount' => $formatRupiah($row['amount']),
                'amount_raw' => $row['amount'],
                'running_total' => $formatRupiah($row['running_total']),
                'running_total_raw' => $row['running_total'],
            ];
        })->toArray();

        $summary = $data['summary'] ?? [];

        return array_merge($baseData, [
            'rows' => $rows,
            'total_income' => $formatRupiah($summary['total_income'] ?? 0),
            'total_income_raw' => $summary['total_income'] ?? 0,
            'total_expense' => $formatRupiah($summary['total_expense'] ?? 0),
            'total_expense_raw' => $summary['total_expense'] ?? 0,
            'net' => $formatRupiah($summary['net'] ?? 0),
            'net_raw' => $summary['net'] ?? 0,
            'net_positive' => ($summary['net'] ?? 0) >= 0,
            'aggregation' => $data['config']['aggregation'] ?? 'monthly',
        ]);
    }
}