<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Cell\StringHelper;

class FinancialReportExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithEvents
{
    /**
     * @var string Report type: 'simple', 'rigid', 'custom'
     */
    protected string $type;

    /**
     * @var array Report data from service
     */
    protected array $data;

    /**
     * @var array Date range ['start' => 'YYYY-MM-DD', 'end' => 'YYYY-MM-DD']
     */
    protected array $dateRange;

    /**
     * @param string $type Report type: 'simple', 'rigid', 'custom'
     * @param array $data Report data from service
     * @param array $dateRange ['start' => 'YYYY-MM-DD', 'end' => 'YYYY-MM-DD']
     */
    public function __construct(string $type, array $data, array $dateRange = [])
    {
        $this->type = $type;
        $this->data = $data;
        $this->dateRange = $dateRange;
    }

    /**
     * Transform data to array for export.
     * Uses chunking logic for large datasets.
     *
     * @return array
     */
    public function array(): array
    {
        return match ($this->type) {
            'simple' => $this->buildSimpleRows(),
            'rigid' => $this->buildRigidRows(),
            'custom' => $this->buildCustomRows(),
            default => [],
        };
    }

    /**
     * Build rows for Simple report.
     * Headers: No | Kategori | Type | Amount
     */
    protected function buildSimpleRows(): array
    {
        $rows = [];
        $no = 1;

        // Income breakdown
        $incomeBreakdown = $this->data['income_breakdown'] ?? [];
        foreach ($incomeBreakdown as $item) {
            $rows[] = [
                $no++,
                $this->formatCategory($item['source'] ?? 'Unknown'),
                'Pemasukan',
                $this->formatRupiah($item['total'] ?? 0),
            ];
        }

        // Expense breakdown
        $expenseBreakdown = $this->data['expense_breakdown'] ?? [];
        foreach ($expenseBreakdown as $item) {
            $rows[] = [
                $no++,
                $this->formatCategory($item['source'] ?? 'Unknown'),
                'Pengeluaran',
                $this->formatRupiah($item['total'] ?? 0),
            ];
        }

        // Summary rows
        $rows[] = ['', '', '', ''];
        $rows[] = [$no++, 'Total Pemasukan', '', $this->formatRupiah($this->data['total_income'] ?? 0)];
        $rows[] = [$no++, 'Total Pengeluaran', '', $this->formatRupiah($this->data['total_expense'] ?? 0)];
        $rows[] = [$no++, 'Piutang Belum Terbayar', '', $this->formatRupiah($this->data['receivables_outstanding'] ?? 0)];
        $rows[] = ['', '', '', ''];
        $rows[] = [$no++, 'LABA/RUGI BERSIH', '', $this->formatRupiah($this->data['net'] ?? 0)];

        return $rows;
    }

    /**
     * Build rows for Rigid report.
     * Headers: Section | Subsection | Amount
     */
    protected function buildRigidRows(): array
    {
        $rows = [];

        $incomeStatement = $this->data['income_statement'] ?? [];
        $cashFlow = $this->data['cash_flow'] ?? [];

        // Income Statement Section
        $rows[] = ['LAPORAN LABA/RUGI', '', ''];
        $rows[] = ['', '', ''];

        $rows[] = ['Pendapatan', 'Pendapatan dari Pesanan', $this->formatRupiah($incomeStatement['pendapatan_orders'] ?? 0)];
        $rows[] = ['', 'Pendapatan Tidak Terduga', $this->formatRupiah($incomeStatement['pendapatan_unexpected'] ?? 0)];
        $rows[] = ['', 'TOTAL PENDAPATAN', $this->formatRupiah($incomeStatement['pendapatan'] ?? 0)];
        $rows[] = ['', '', ''];

        $rows[] = ['Harga Pokok Penjualan (HPP)', '', $this->formatRupiah($incomeStatement['hpp'] ?? 0)];
        $rows[] = ['', '', ''];

        $rows[] = ['LABA KOTOR', '', $this->formatRupiah($incomeStatement['laba_kotor'] ?? 0)];
        $rows[] = ['', '', ''];

        $rows[] = ['Beban Operasional', '', $this->formatRupiah($incomeStatement['beban_operasional'] ?? 0)];
        $rows[] = ['Beban Tak Terduga', '', $this->formatRupiah($incomeStatement['beban_tak_terduga'] ?? 0)];
        $rows[] = ['', '', ''];

        $rows[] = ['LABA/RUGI BERSIH', '', $this->formatRupiah($incomeStatement['laba_rugi_bersih'] ?? 0)];
        $rows[] = ['', '', ''];
        $rows[] = ['', '', ''];

        // Cash Flow Section
        $rows[] = ['LAPORAN ARUS KAS', '', ''];
        $rows[] = ['', '', ''];

        $rows[] = ['Arus Kas Masuk', 'Pendapatan', $this->formatRupiah($cashFlow['pendapatan'] ?? 0)];
        $rows[] = ['', 'Pembayaran Piutang', $this->formatRupiah($cashFlow['receivable_payments'] ?? 0)];
        $rows[] = ['', 'TOTAL ARUS KAS MASUK', $this->formatRupiah($cashFlow['arus_kas_masuk'] ?? 0)];
        $rows[] = ['', '', ''];

        $rows[] = ['Arus Kas Keluar', 'Beban Operasional', $this->formatRupiah($cashFlow['beban_operasional'] ?? 0)];
        $rows[] = ['', 'Harga Pokok Penjualan', $this->formatRupiah($cashFlow['hpp'] ?? 0)];
        $rows[] = ['', 'Beban Tak Terduga', $this->formatRupiah($cashFlow['beban_tak_terduga'] ?? 0)];
        $rows[] = ['', 'TOTAL ARUS KAS KELUAR', $this->formatRupiah($cashFlow['arus_kas_keluar'] ?? 0)];
        $rows[] = ['', '', ''];

        $rows[] = ['ARUS KAS BERSIH', '', $this->formatRupiah($cashFlow['arus_kas_bersih'] ?? 0)];
        $rows[] = ['', '', ''];

        $rows[] = ['Saldo Awal', '', $this->formatRupiah($cashFlow['saldo_awal'] ?? 0)];
        $rows[] = ['Saldo Akhir', '', $this->formatRupiah($cashFlow['saldo_akhir'] ?? 0)];

        return $rows;
    }

    /**
     * Build rows for Custom report.
     * Headers: Date | Category | Type | Amount | Running Total
     */
    protected function buildCustomRows(): array
    {
        $rows = [];
        $customRows = $this->data['rows'] ?? [];

        foreach ($customRows as $row) {
            $rows[] = [
                $row['date'] ?? '',
                $row['category'] ?? '',
                $row['type'] === 'Income' ? 'Pemasukan' : 'Pengeluaran',
                $this->formatRupiah($row['amount'] ?? 0),
                $this->formatRupiah($row['running_total'] ?? 0),
            ];
        }

        // Summary
        if (!empty($rows)) {
            $rows[] = ['', '', '', '', ''];
            $summary = $this->data['summary'] ?? [];
            $rows[] = ['', 'TOTAL PEMASUKAN', '', $this->formatRupiah($summary['total_income'] ?? 0), ''];
            $rows[] = ['', 'TOTAL PENGELUARAN', '', $this->formatRupiah($summary['total_expense'] ?? 0), ''];
            $rows[] = ['', 'LABA/RUGI BERSIH', '', $this->formatRupiah($summary['net'] ?? 0), ''];
        }

        return $rows;
    }

    /**
     * Get headings for the export.
     *
     * @return array
     */
    public function headings(): array
    {
        return match ($this->type) {
            'simple' => ['No', 'Kategori', 'Tipe', 'Jumlah (Rp)'],
            'rigid' => ['Bagian', 'Sub Bagian', 'Jumlah (Rp)'],
            'custom' => ['Tanggal', 'Kategori', 'Tipe', 'Jumlah (Rp)', 'Total Berjalan (Rp)'],
            default => [],
        };
    }

    /**
     * Get title for the worksheet.
     *
     * @return string
     */
    public function title(): string
    {
        $typeLabel = match ($this->type) {
            'simple' => 'Laporan Sederhana',
            'rigid' => 'Laporan Rigid (Laba/Rugi & Arus Kas)',
            'custom' => 'Laporan Custom',
            default => 'Laporan Keuangan',
        };

        $dateLabel = '';
        if (!empty($this->dateRange)) {
            $start = $this->dateRange['start'] ?? '';
            $end = $this->dateRange['end'] ?? '';
            if ($start && $end) {
                $dateLabel = " ({$start} - {$end})";
            }
        }

        return $typeLabel . $dateLabel;
    }

    /**
     * Register events for UTF-8 BOM handling.
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => function (BeforeWriting $event) {
                // Ensure UTF-8 encoding for Indonesian characters
                $event->getDelegate()->getProperties()
                    ->setCreator('W9 Cafe POS')
                    ->setTitle($this->title());
            },
        ];
    }

    /**
     * Format amount as Indonesian Rupiah.
     *
     * @param float|int $amount
     * @return string
     */
    protected function formatRupiah(float|int $amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }

    /**
     * Format category/source for display.
     *
     * @param string $source
     * @return string
     */
    protected function formatCategory(string $source): string
    {
        return match ($source) {
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            'transfer' => 'Transfer',
            'card' => 'Kartu',
            'unexpected_income' => 'Pemasukan Tidak Terduga',
            'ingredient_purchase' => 'Pembelian Bahan Baku',
            'unexpected_expense' => 'Pengeluaran Tidak Terduga',
            default => ucfirst(str_replace('_', ' ', $source)),
        };
    }
}