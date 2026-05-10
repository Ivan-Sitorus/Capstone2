<?php

namespace App\DTO;

class ReportData
{
    public const TYPE_SIMPLE = 'simple';
    public const TYPE_RIGID  = 'rigid';
    public const TYPE_CUSTOM = 'custom';

    /**
     * @param  string         $type         simple|rigid|custom
     * @param  string         $title        Report display title
     * @param  string         $dateStart    Start date (Y-m-d)
     * @param  string         $dateEnd      End date (Y-m-d)
     * @param  string         $aggregation  daily|monthly
     * @param  SummaryItem[]  $summary      Aggregated summary items
     * @param  ReportRow[]    $rows         Detail rows
     * @param  array          $config       Original config (custom report only)
     */
    public function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly string $dateStart,
        public readonly string $dateEnd,
        public readonly string $aggregation,
        public readonly array  $summary = [],
        public readonly array  $rows = [],
        public readonly array  $config = [],
    ) {
    }

    // ─── Factory: SimpleReportService output ──────────────────────────

    public static function fromSimpleReport(array $data, string $dateStart, string $dateEnd): self
    {
        $rows = [];

        foreach ($data['income_breakdown'] ?? [] as $item) {
            $rows[] = new ReportRow(
                date:     '',
                category: $item['source'] ?? 'Unknown',
                type:     ReportRow::TYPE_INCOME,
                amount:   (float) ($item['total'] ?? 0),
                rawData:  $item,
            );
        }

        foreach ($data['expense_breakdown'] ?? [] as $item) {
            $rows[] = new ReportRow(
                date:     '',
                category: $item['source'] ?? 'Unknown',
                type:     ReportRow::TYPE_EXPENSE,
                amount:   (float) ($item['total'] ?? 0),
                rawData:  $item,
            );
        }

        $totalIncome  = (float) ($data['total_income'] ?? 0);
        $totalExpense = (float) ($data['total_expense'] ?? 0);
        $net          = (float) ($data['net'] ?? 0);

        $rows[] = new ReportRow(
            date:     '',
            category: 'Net',
            type:     ReportRow::TYPE_GRAND_TOTAL,
            amount:   $net,
            isBold:   true,
        );

        $summary = [
            new SummaryItem('Total Pendapatan', self::formatCurrency($totalIncome), $totalIncome),
            new SummaryItem('Total Pengeluaran', self::formatCurrency($totalExpense), $totalExpense),
            new SummaryItem('Net', self::formatCurrency($net), $net, true),
        ];

        $title = "Simple Report {$dateStart} — {$dateEnd}";

        return new self(
            type:        self::TYPE_SIMPLE,
            title:       $title,
            dateStart:   $dateStart,
            dateEnd:     $dateEnd,
            aggregation: 'daily',
            summary:     $summary,
            rows:        $rows,
        );
    }

    // ─── Factory: RigidReportService output ───────────────────────────

    public static function fromRigidReport(array $data, string $dateStart, string $dateEnd): self
    {
        $is  = $data['income_statement'] ?? [];
        $cf  = $data['cash_flow'] ?? [];
        $meta = $data['meta'] ?? [];

        $pendapatan            = (float) ($is['pendapatan'] ?? 0);
        $pendapatanOrders      = (float) ($is['pendapatan_orders'] ?? 0);
        $pendapatanUnexpected  = (float) ($is['pendapatan_unexpected'] ?? 0);
        $hpp                   = (float) ($is['hpp'] ?? 0);
        $labaKotor             = (float) ($is['laba_kotor'] ?? 0);
        $bebanOperasional      = (float) ($is['beban_operasional'] ?? 0);
        $bebanTakTerduga       = (float) ($is['beban_tak_terduga'] ?? 0);
        $labaRugiBersih        = (float) ($is['laba_rugi_bersih'] ?? 0);

        $arusKasMasuk      = (float) ($cf['arus_kas_masuk'] ?? 0);
        $arusKasKeluar     = (float) ($cf['arus_kas_keluar'] ?? 0);
        $arusKasBersih     = (float) ($cf['arus_kas_bersih'] ?? 0);
        $receivablePayments = (float) ($cf['receivable_payments'] ?? 0);
        $saldoAwal          = (float) ($cf['saldo_awal'] ?? 0);
        $saldoAkhir         = (float) ($cf['saldo_akhir'] ?? 0);

        $rows = [];

        // ── Income Statement section ──

        $rows[] = new ReportRow(
            date: '', category: 'Laporan Laba Rugi', type: ReportRow::TYPE_SECTION,
            amount: 0, isBold: true,
        );

        $rows[] = new ReportRow(
            date: '', category: 'Pendapatan', type: ReportRow::TYPE_INCOME,
            amount: $pendapatan,
        );

        $rows[] = new ReportRow(
            date: '', category: 'Pendapatan dari Pesanan', type: ReportRow::TYPE_INCOME,
            amount: $pendapatanOrders, indentLevel: 1,
        );

        $rows[] = new ReportRow(
            date: '', category: 'Pendapatan Tak Terduga', type: ReportRow::TYPE_INCOME,
            amount: $pendapatanUnexpected, indentLevel: 1,
        );

        $rows[] = new ReportRow(
            date: '', category: 'HPP', type: ReportRow::TYPE_EXPENSE,
            amount: $hpp,
        );

        $rows[] = new ReportRow(
            date: '', category: 'Laba Kotor', type: ReportRow::TYPE_TOTAL,
            amount: $labaKotor, isBold: true,
        );

        $rows[] = new ReportRow(
            date: '', category: 'Beban Operasional', type: ReportRow::TYPE_EXPENSE,
            amount: $bebanOperasional,
        );

        $rows[] = new ReportRow(
            date: '', category: 'Beban Tak Terduga', type: ReportRow::TYPE_EXPENSE,
            amount: $bebanTakTerduga,
        );

        $rows[] = new ReportRow(
            date: '', category: 'Laba Rugi Bersih', type: ReportRow::TYPE_GRAND_TOTAL,
            amount: $labaRugiBersih, isBold: true,
        );

        // ── Cash Flow section ──

        $rows[] = new ReportRow(
            date: '', category: 'Laporan Arus Kas', type: ReportRow::TYPE_SECTION,
            amount: 0, isBold: true,
        );

        $rows[] = new ReportRow(
            date: '', category: 'Arus Kas Masuk', type: ReportRow::TYPE_INCOME,
            amount: $arusKasMasuk,
        );

        $rows[] = new ReportRow(
            date: '', category: 'Pendapatan', type: ReportRow::TYPE_INCOME,
            amount: $pendapatan, indentLevel: 1,
        );

        $rows[] = new ReportRow(
            date: '', category: 'Penerimaan Piutang', type: ReportRow::TYPE_INCOME,
            amount: $receivablePayments, indentLevel: 1,
        );

        $rows[] = new ReportRow(
            date: '', category: 'Arus Kas Keluar', type: ReportRow::TYPE_EXPENSE,
            amount: $arusKasKeluar,
        );

        $rows[] = new ReportRow(
            date: '', category: 'Beban Operasional', type: ReportRow::TYPE_EXPENSE,
            amount: $bebanOperasional, indentLevel: 1,
        );

        $rows[] = new ReportRow(
            date: '', category: 'HPP', type: ReportRow::TYPE_EXPENSE,
            amount: $hpp, indentLevel: 1,
        );

        $rows[] = new ReportRow(
            date: '', category: 'Beban Tak Terduga', type: ReportRow::TYPE_EXPENSE,
            amount: $bebanTakTerduga, indentLevel: 1,
        );

        $rows[] = new ReportRow(
            date: '', category: 'Arus Kas Bersih', type: ReportRow::TYPE_TOTAL,
            amount: $arusKasBersih,
        );

        $rows[] = new ReportRow(
            date: '', category: 'Saldo Awal', type: ReportRow::TYPE_INCOME,
            amount: $saldoAwal,
        );

        $rows[] = new ReportRow(
            date: '', category: 'Saldo Akhir', type: ReportRow::TYPE_GRAND_TOTAL,
            amount: $saldoAkhir, isBold: true,
        );

        $totalIncome  = $pendapatan + $arusKasMasuk + $saldoAwal;
        $totalExpense = $hpp + $bebanOperasional + $bebanTakTerduga + $arusKasKeluar;

        $summary = [
            new SummaryItem('Total Pendapatan', self::formatCurrency($totalIncome), $totalIncome),
            new SummaryItem('Total Pengeluaran', self::formatCurrency($totalExpense), $totalExpense),
            new SummaryItem('Laba Rugi Bersih', self::formatCurrency($labaRugiBersih), $labaRugiBersih, true),
            new SummaryItem('Arus Kas Bersih', self::formatCurrency($arusKasBersih), $arusKasBersih),
        ];

        $title = "Rigid Report {$dateStart} — {$dateEnd}";

        return new self(
            type:        self::TYPE_RIGID,
            title:       $title,
            dateStart:   $dateStart,
            dateEnd:     $dateEnd,
            aggregation: 'daily',
            summary:     $summary,
            rows:        $rows,
        );
    }

    // ─── Factory: CustomReportService output ──────────────────────────

    public static function fromCustomReport(array $data, string $dateStart, string $dateEnd): self
    {
        $config      = $data['config'] ?? [];
        $sourceRows  = $data['rows'] ?? [];
        $sourceSummary = $data['summary'] ?? [];
        $aggregation = $config['aggregation'] ?? 'monthly';

        $rows = [];
        foreach ($sourceRows as $item) {
            $rows[] = new ReportRow(
                date:         $item['date'] ?? '',
                category:     $item['category'] ?? '',
                type:         $item['type'] ?? ReportRow::TYPE_INCOME,
                amount:       (float) ($item['amount'] ?? 0),
                runningTotal: array_key_exists('running_total', $item)
                    ? (float) $item['running_total'] : null,
                indentLevel:  (int) ($item['indent_level'] ?? 0),
                isBold:       (bool) ($item['is_bold'] ?? false),
                rawData:      $item,
            );
        }

        $summary = [
            new SummaryItem(
                'Total Pendapatan',
                self::formatCurrency((float) ($sourceSummary['total_income'] ?? 0)),
                (float) ($sourceSummary['total_income'] ?? 0),
            ),
            new SummaryItem(
                'Total Pengeluaran',
                self::formatCurrency((float) ($sourceSummary['total_expense'] ?? 0)),
                (float) ($sourceSummary['total_expense'] ?? 0),
            ),
            new SummaryItem(
                'Net',
                self::formatCurrency((float) ($sourceSummary['net'] ?? 0)),
                (float) ($sourceSummary['net'] ?? 0),
                true,
            ),
        ];

        $title = "Custom Report {$dateStart} — {$dateEnd}";

        return new self(
            type:        self::TYPE_CUSTOM,
            title:       $title,
            dateStart:   $dateStart,
            dateEnd:     $dateEnd,
            aggregation: $aggregation,
            summary:     $summary,
            rows:        $rows,
            config:      $config,
        );
    }

    // ─── Backward-compatible: GeneratedReport.result JSON ─────────────

    public static function fromGeneratedReport(array $result): self
    {
        $type        = $result['type'] ?? self::TYPE_SIMPLE;
        $title       = $result['title'] ?? '';
        $dateStart   = $result['date_start'] ?? '';
        $dateEnd     = $result['date_end'] ?? '';
        $aggregation = $result['aggregation'] ?? 'daily';
        $config      = $result['config'] ?? [];

        $rows = [];
        foreach ($result['rows'] ?? [] as $item) {
            $rows[] = ReportRow::fromArray($item);
        }

        $summary = [];
        foreach ($result['summary'] ?? [] as $item) {
            $summary[] = SummaryItem::fromArray($item);
        }

        return new self(
            type:        $type,
            title:       $title,
            dateStart:   $dateStart,
            dateEnd:     $dateEnd,
            aggregation: $aggregation,
            summary:     $summary,
            rows:        $rows,
            config:      $config,
        );
    }

    // ─── Serialization ────────────────────────────────────────────────

    public function toArray(): array
    {
        $rowsArray = [];
        foreach ($this->rows as $row) {
            $rowsArray[] = $row->toArray();
        }

        $summaryArray = [];
        foreach ($this->summary as $item) {
            $summaryArray[] = $item->toArray();
        }

        return [
            'type'        => $this->type,
            'title'       => $this->title,
            'date_start'  => $this->dateStart,
            'date_end'    => $this->dateEnd,
            'aggregation' => $this->aggregation,
            'total_income'  => $this->getTotalIncome(),
            'total_expense' => $this->getTotalExpense(),
            'net'           => $this->getNet(),
            'rows'        => $rowsArray,
            'summary'     => $summaryArray,
            'config'      => $this->config,
        ];
    }

    // ─── Aggregate helpers ────────────────────────────────────────────

    public function getTotalIncome(): float
    {
        $sum = 0.0;
        foreach ($this->rows as $row) {
            if ($row->isIncome()) {
                $sum += $row->amount;
            }
        }
        return round($sum, 2);
    }

    public function getTotalExpense(): float
    {
        $sum = 0.0;
        foreach ($this->rows as $row) {
            if ($row->isExpense()) {
                $sum += $row->amount;
            }
        }
        return round($sum, 2);
    }

    public function getNet(): float
    {
        return round($this->getTotalIncome() - $this->getTotalExpense(), 2);
    }

    // ─── Internal ─────────────────────────────────────────────────────

    private static function formatCurrency(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
