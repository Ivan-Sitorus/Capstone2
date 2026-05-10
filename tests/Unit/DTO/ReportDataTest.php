<?php

namespace Tests\Unit\DTO;

use App\DTO\ReportData;
use App\DTO\ReportRow;
use App\DTO\SummaryItem;
use PHPUnit\Framework\TestCase;

class ReportDataTest extends TestCase
{
    // ─── Simple Report ────────────────────────────────────────────────

    public function test_from_simple_report_service_output_creates_valid_dto(): void
    {
        $data = [
            'total_income'          => 150000.00,
            'total_expense'         => 75000.00,
            'net'                   => 75000.00,
            'income_breakdown'      => [
                ['source' => 'cash', 'total' => 100000, 'count' => 5],
                ['source' => 'qris', 'total' => 50000, 'count' => 3],
            ],
            'expense_breakdown'     => [
                ['source' => 'ingredient_purchase', 'total' => 50000, 'count' => 2],
                ['source' => 'unexpected_expense', 'total' => 25000, 'count' => 1],
            ],
            'receivables_outstanding' => 12000.00,
            'date_range' => ['start' => '2026-05-01', 'end' => '2026-05-08'],
        ];

        $dto = ReportData::fromSimpleReport($data, '2026-05-01', '2026-05-08');

        $this->assertSame(ReportData::TYPE_SIMPLE, $dto->type);
        $this->assertSame('2026-05-01', $dto->dateStart);
        $this->assertSame('2026-05-08', $dto->dateEnd);
        $this->assertCount(5, $dto->rows);
        $this->assertCount(3, $dto->summary);

        $firstRow = $dto->rows[0];
        $this->assertSame('cash', $firstRow->category);
        $this->assertSame(ReportRow::TYPE_INCOME, $firstRow->type);
        $this->assertSame(100000.0, $firstRow->amount);
        $this->assertTrue($firstRow->isIncome());

        $expenseRow = $dto->rows[2];
        $this->assertSame('ingredient_purchase', $expenseRow->category);
        $this->assertSame(ReportRow::TYPE_EXPENSE, $expenseRow->type);
        $this->assertTrue($expenseRow->isExpense());

        $netRow = $dto->rows[4];
        $this->assertSame(ReportRow::TYPE_GRAND_TOTAL, $netRow->type);
        $this->assertTrue($netRow->isGrandTotal());
        $this->assertTrue($netRow->isBold);

        $this->assertEquals(150000.0, $dto->getTotalIncome());
        $this->assertEquals(75000.0, $dto->getTotalExpense());
        $this->assertEquals(75000.0, $dto->getNet());

        $highlightedSummary = $dto->summary[2];
        $this->assertSame('Net', $highlightedSummary->label);
        $this->assertTrue($highlightedSummary->isHighlighted);
    }

    // ─── Rigid Report ─────────────────────────────────────────────────

    public function test_from_rigid_report_service_output_creates_valid_dto(): void
    {
        $data = [
            'meta' => [
                'date_start'   => '2026-05-01',
                'date_end'     => '2026-05-08',
                'generated_at' => '2026-05-08 10:00:00',
                'type'         => 'rigid',
            ],
            'income_statement' => [
                'pendapatan'            => 200000.00,
                'pendapatan_orders'     => 150000.00,
                'pendapatan_unexpected' => 50000.00,
                'hpp'                   => 80000.00,
                'laba_kotor'            => 120000.00,
                'beban_operasional'     => 30000.00,
                'beban_tak_terduga'     => 20000.00,
                'laba_rugi_bersih'      => 70000.00,
            ],
            'cash_flow' => [
                'arus_kas_masuk'      => 220000.00,
                'pendapatan'          => 200000.00,
                'receivable_payments' => 20000.00,
                'arus_kas_keluar'     => 130000.00,
                'beban_operasional'   => 30000.00,
                'hpp'                 => 80000.00,
                'beban_tak_terduga'   => 20000.00,
                'arus_kas_bersih'     => 90000.00,
                'saldo_awal'          => 0.0,
                'saldo_akhir'         => 90000.00,
            ],
        ];

        $dto = ReportData::fromRigidReport($data, '2026-05-01', '2026-05-08');

        $this->assertSame(ReportData::TYPE_RIGID, $dto->type);
        $this->assertCount(4, $dto->summary);

        $sectionRow = $dto->rows[0];
        $this->assertSame(ReportRow::TYPE_SECTION, $sectionRow->type);
        $this->assertSame('Laporan Laba Rugi', $sectionRow->category);
        $this->assertTrue($sectionRow->isSection());
        $this->assertTrue($sectionRow->isBold);

        $labaRugiRow = $dto->rows[8];
        $this->assertSame('Laba Rugi Bersih', $labaRugiRow->category);
        $this->assertSame(ReportRow::TYPE_GRAND_TOTAL, $labaRugiRow->type);
        $this->assertTrue($labaRugiRow->isGrandTotal());
        $this->assertTrue($labaRugiRow->isBold);
        $this->assertSame(70000.0, $labaRugiRow->amount);

        $cashFlowSection = $dto->rows[9];
        $this->assertSame('Laporan Arus Kas', $cashFlowSection->category);
        $this->assertSame(ReportRow::TYPE_SECTION, $cashFlowSection->type);

        $saldoAkhirRow = $dto->rows[19];
        $this->assertSame('Saldo Akhir', $saldoAkhirRow->category);
        $this->assertSame(ReportRow::TYPE_GRAND_TOTAL, $saldoAkhirRow->type);
        $this->assertTrue($saldoAkhirRow->isBold);
    }

    // ─── Custom Report ────────────────────────────────────────────────

    public function test_from_custom_report_service_output_creates_valid_dto(): void
    {
        $data = [
            'config' => [
                'date_start'  => '2026-05-01',
                'date_end'    => '2026-05-08',
                'categories'  => ['menu:1'],
                'aggregation' => 'daily',
            ],
            'rows' => [
                [
                    'date'          => '2026-05-01',
                    'category'      => 'Kopi',
                    'type'          => 'Income',
                    'amount'        => 50000.0,
                    'running_total' => 50000.0,
                ],
                [
                    'date'          => '2026-05-01',
                    'category'      => 'Bahan Baku',
                    'type'          => 'Expense',
                    'amount'        => 20000.0,
                    'running_total' => 30000.0,
                ],
                [
                    'date'          => '2026-05-02',
                    'category'      => 'Kopi',
                    'type'          => 'Income',
                    'amount'        => 60000.0,
                    'running_total' => 90000.0,
                ],
            ],
            'summary' => [
                'total_income'  => 110000.00,
                'total_expense' => 20000.00,
                'net'           => 90000.00,
            ],
        ];

        $dto = ReportData::fromCustomReport($data, '2026-05-01', '2026-05-08');

        $this->assertSame(ReportData::TYPE_CUSTOM, $dto->type);
        $this->assertSame('daily', $dto->aggregation);
        $this->assertCount(3, $dto->rows);
        $this->assertCount(3, $dto->summary);
        $this->assertNotEmpty($dto->config);

        $firstRow = $dto->rows[0];
        $this->assertSame('2026-05-01', $firstRow->date);
        $this->assertSame('Kopi', $firstRow->category);
        $this->assertSame(ReportRow::TYPE_INCOME, $firstRow->type);
        $this->assertSame(50000.0, $firstRow->amount);
        $this->assertSame(50000.0, $firstRow->runningTotal);

        $this->assertEquals(110000.0, $dto->getTotalIncome());
        $this->assertEquals(20000.0, $dto->getTotalExpense());
        $this->assertEquals(90000.0, $dto->getNet());
    }

    // ─── Backward Compatibility ───────────────────────────────────────

    public function test_backward_compatible_with_generated_report_json(): void
    {
        $result = [
            'type'        => 'simple',
            'title'       => 'Test Report 2026-05-01 — 2026-05-08',
            'date_start'  => '2026-05-01',
            'date_end'    => '2026-05-08',
            'aggregation' => 'daily',
            'total_income'  => 100000.00,
            'total_expense' => 40000.00,
            'net'           => 60000.00,
            'rows' => [
                [
                    'date'          => '2026-05-01',
                    'category'      => 'cash',
                    'type'          => 'Income',
                    'amount'        => 100000.0,
                    'running_total' => null,
                    'indent_level'  => 0,
                    'is_bold'       => false,
                    'raw_data'      => [],
                ],
                [
                    'date'          => '',
                    'category'      => 'Net',
                    'type'          => 'GrandTotal',
                    'amount'        => 60000.0,
                    'running_total' => null,
                    'indent_level'  => 0,
                    'is_bold'       => true,
                    'raw_data'      => [],
                ],
            ],
            'summary' => [
                [
                    'label'          => 'Total Pendapatan',
                    'formatted_value' => 'Rp 100.000',
                    'raw_value'      => 100000.0,
                    'is_highlighted' => false,
                ],
            ],
            'config' => [],
        ];

        $dto = ReportData::fromGeneratedReport($result);

        $this->assertSame('simple', $dto->type);
        $this->assertSame('Test Report 2026-05-01 — 2026-05-08', $dto->title);
        $this->assertCount(2, $dto->rows);
        $this->assertCount(1, $dto->summary);

        $firstRow = $dto->rows[0];
        $this->assertSame('2026-05-01', $firstRow->date);
        $this->assertSame('cash', $firstRow->category);
        $this->assertSame(ReportRow::TYPE_INCOME, $firstRow->type);

        $summaryItem = $dto->summary[0];
        $this->assertSame('Total Pendapatan', $summaryItem->label);
        $this->assertSame('Rp 100.000', $summaryItem->formattedValue);
        $this->assertSame(100000.0, $summaryItem->rawValue);
    }

    // ─── Indent Levels ────────────────────────────────────────────────

    public function test_rows_have_correct_indent_levels(): void
    {
        $data = [
            'meta' => [
                'date_start'   => '2026-05-01',
                'date_end'     => '2026-05-08',
                'generated_at' => '2026-05-08 10:00:00',
                'type'         => 'rigid',
            ],
            'income_statement' => [
                'pendapatan'            => 200000,
                'pendapatan_orders'     => 150000,
                'pendapatan_unexpected' => 50000,
                'hpp'                   => 80000,
                'laba_kotor'            => 120000,
                'beban_operasional'     => 30000,
                'beban_tak_terduga'     => 20000,
                'laba_rugi_bersih'      => 70000,
            ],
            'cash_flow' => [
                'arus_kas_masuk'      => 220000,
                'pendapatan'          => 200000,
                'receivable_payments' => 20000,
                'arus_kas_keluar'     => 130000,
                'beban_operasional'   => 30000,
                'hpp'                 => 80000,
                'beban_tak_terduga'   => 20000,
                'arus_kas_bersih'     => 90000,
                'saldo_awal'          => 0,
                'saldo_akhir'         => 90000,
            ],
        ];

        $dto = ReportData::fromRigidReport($data, '2026-05-01', '2026-05-08');

        // Pendapatan parent row is indent 0
        $this->assertSame(0, $dto->rows[1]->indentLevel);
        $this->assertSame('Pendapatan', $dto->rows[1]->category);

        // Pendapatan sub-rows are indent 1
        $this->assertSame(1, $dto->rows[2]->indentLevel);
        $this->assertSame('Pendapatan dari Pesanan', $dto->rows[2]->category);

        $this->assertSame(1, $dto->rows[3]->indentLevel);
        $this->assertSame('Pendapatan Tak Terduga', $dto->rows[3]->category);

        // HPP is indent 0
        $this->assertSame(0, $dto->rows[4]->indentLevel);
        $this->assertSame('HPP', $dto->rows[4]->category);

        // Cash flow sub-rows indent 1
        $pendapatanCFIdx = 11;
        $this->assertSame(1, $dto->rows[$pendapatanCFIdx]->indentLevel);
        $this->assertSame('Pendapatan', $dto->rows[$pendapatanCFIdx]->category);

        // Arus Kas Keluar sub-rows indent 1
        $this->assertSame(0, $dto->rows[13]->indentLevel);
        $this->assertSame('Arus Kas Keluar', $dto->rows[13]->category);

        $this->assertSame(1, $dto->rows[14]->indentLevel);
        $this->assertSame('Beban Operasional', $dto->rows[14]->category);
    }

    // ─── Grand Total Markers ──────────────────────────────────────────

    public function test_grand_total_row_marked_correctly(): void
    {
        // Simple report GrandTotal
        $simpleData = [
            'total_income' => 100000,
            'total_expense' => 40000,
            'net' => 60000,
            'income_breakdown' => [['source' => 'cash', 'total' => 100000, 'count' => 1]],
            'expense_breakdown' => [['source' => 'ingredient_purchase', 'total' => 40000, 'count' => 1]],
            'receivables_outstanding' => 0,
            'date_range' => ['start' => '2026-05-01', 'end' => '2026-05-08'],
        ];
        $simpleDto = ReportData::fromSimpleReport($simpleData, '2026-05-01', '2026-05-08');

        $grandTotalRows = array_filter($simpleDto->rows, fn (ReportRow $r) => $r->isGrandTotal());
        $this->assertCount(1, $grandTotalRows);
        $gt = reset($grandTotalRows);
        $this->assertTrue($gt->isBold);
        $this->assertSame(60000.0, $gt->amount);

        // Rigid report GrandTotals
        $rigidData = [
            'meta' => ['date_start' => '2026-05-01', 'date_end' => '2026-05-08',
                       'generated_at' => '2026-05-08 10:00:00', 'type' => 'rigid'],
            'income_statement' => [
                'pendapatan' => 200000, 'pendapatan_orders' => 150000,
                'pendapatan_unexpected' => 50000, 'hpp' => 80000,
                'laba_kotor' => 120000, 'beban_operasional' => 30000,
                'beban_tak_terduga' => 20000, 'laba_rugi_bersih' => 70000,
            ],
            'cash_flow' => [
                'arus_kas_masuk' => 220000, 'pendapatan' => 200000,
                'receivable_payments' => 20000, 'arus_kas_keluar' => 130000,
                'beban_operasional' => 30000, 'hpp' => 80000,
                'beban_tak_terduga' => 20000, 'arus_kas_bersih' => 90000,
                'saldo_awal' => 0, 'saldo_akhir' => 90000,
            ],
        ];
        $rigidDto = ReportData::fromRigidReport($rigidData, '2026-05-01', '2026-05-08');

        $grandTotalRows = array_filter($rigidDto->rows, fn (ReportRow $r) => $r->isGrandTotal());
        $this->assertCount(2, $grandTotalRows);

        foreach ($grandTotalRows as $gt) {
            $this->assertTrue($gt->isBold, "GrandTotal row '{$gt->category}' should be bold");
            $this->assertTrue($gt->isGrandTotal());
        }
    }

    // ─── Summary Items ────────────────────────────────────────────────

    public function test_summary_items_created_correctly(): void
    {
        $item = new SummaryItem('Net Income', 'Rp 75.000', 75000.0, true);
        $this->assertSame('Net Income', $item->label);
        $this->assertSame('Rp 75.000', $item->formattedValue);
        $this->assertSame(75000.0, $item->rawValue);
        $this->assertTrue($item->isHighlighted);

        $fromArray = SummaryItem::fromArray([
            'label'          => 'Total',
            'formatted_value' => 'Rp 100.000',
            'raw_value'      => 100000.0,
            'is_highlighted' => false,
        ]);
        $this->assertSame('Total', $fromArray->label);
        $this->assertSame('Rp 100.000', $fromArray->formattedValue);
        $this->assertSame(100000.0, $fromArray->rawValue);
        $this->assertFalse($fromArray->isHighlighted);
    }

    // ─── toArray Roundtrip ────────────────────────────────────────────

    public function test_to_array_roundtrip_maintains_data(): void
    {
        $original = [
            'type'        => 'custom',
            'title'       => 'Custom May Report',
            'date_start'  => '2026-05-01',
            'date_end'    => '2026-05-08',
            'aggregation' => 'daily',
            'total_income'  => 110000.0,
            'total_expense' => 20000.0,
            'net'           => 90000.0,
            'rows' => [
                [
                    'date'          => '2026-05-01',
                    'category'      => 'Kopi',
                    'type'          => 'Income',
                    'amount'        => 110000.0,
                    'running_total' => 110000.0,
                    'indent_level'  => 0,
                    'is_bold'       => false,
                    'raw_data'      => [],
                ],
                [
                    'date'          => '2026-05-01',
                    'category'      => 'Bahan Baku',
                    'type'          => 'Expense',
                    'amount'        => 20000.0,
                    'running_total' => 90000.0,
                    'indent_level'  => 0,
                    'is_bold'       => false,
                    'raw_data'      => [],
                ],
            ],
            'summary' => [
                [
                    'label'          => 'Total Pendapatan',
                    'formatted_value' => 'Rp 110.000',
                    'raw_value'      => 110000.0,
                    'is_highlighted' => false,
                ],
                [
                    'label'          => 'Total Pengeluaran',
                    'formatted_value' => 'Rp 20.000',
                    'raw_value'      => 20000.0,
                    'is_highlighted' => false,
                ],
                [
                    'label'          => 'Net',
                    'formatted_value' => 'Rp 90.000',
                    'raw_value'      => 90000.0,
                    'is_highlighted' => true,
                ],
            ],
            'config' => ['aggregation' => 'daily'],
        ];

        // Simulate loading from GeneratedReport.result
        $dto = ReportData::fromGeneratedReport($original);
        $serialized = $dto->toArray();

        // Re-load from serialized
        $reloaded = ReportData::fromGeneratedReport($serialized);

        $this->assertSame($original['type'], $reloaded->type);
        $this->assertSame($original['title'], $reloaded->title);
        $this->assertSame($original['date_start'], $reloaded->dateStart);
        $this->assertSame($original['date_end'], $reloaded->dateEnd);
        $this->assertSame($original['aggregation'], $reloaded->aggregation);

        $this->assertCount(2, $reloaded->rows);
        $this->assertSame('Kopi', $reloaded->rows[0]->category);
        $this->assertSame(110000.0, $reloaded->rows[0]->amount);
        $this->assertSame(110000.0, $reloaded->rows[0]->runningTotal);

        $this->assertCount(3, $reloaded->summary);
        $this->assertSame('Net', $reloaded->summary[2]->label);
        $this->assertTrue($reloaded->summary[2]->isHighlighted);

        $this->assertSame($original['total_income'], $reloaded->getTotalIncome());
        $this->assertSame($original['total_expense'], $reloaded->getTotalExpense());
        $this->assertSame($original['net'], $reloaded->getNet());
    }

    // ─── ReportRow standalone ─────────────────────────────────────────

    public function test_report_row_factory_and_helpers(): void
    {
        $row = new ReportRow(
            date: '2026-05-01',
            category: 'Kopi',
            type: ReportRow::TYPE_INCOME,
            amount: 50000.0,
            runningTotal: 50000.0,
            indentLevel: 0,
            isBold: false,
            rawData: ['source' => 'orders'],
        );

        $this->assertTrue($row->isIncome());
        $this->assertFalse($row->isExpense());
        $this->assertFalse($row->isTotal());
        $this->assertFalse($row->isSection());
        $this->assertFalse($row->isGrandTotal());

        $fromArr = ReportRow::fromArray([
            'date'          => '2026-05-02',
            'category'      => 'Bahan Baku',
            'type'          => 'Expense',
            'amount'        => 20000,
            'running_total' => 30000,
            'indent_level'  => 1,
            'is_bold'       => true,
        ]);

        $this->assertTrue($fromArr->isExpense());
        $this->assertSame(1, $fromArr->indentLevel);
        $this->assertTrue($fromArr->isBold);
        $this->assertSame(20000.0, $fromArr->amount);

        $array = $fromArr->toArray();
        $this->assertSame('2026-05-02', $array['date']);
        $this->assertSame('Bahan Baku', $array['category']);
        $this->assertSame('Expense', $array['type']);
        $this->assertSame(1, $array['indent_level']);
    }

    public function test_report_row_section_type(): void
    {
        $section = new ReportRow(
            date: '',
            category: 'Laporan Laba Rugi',
            type: ReportRow::TYPE_SECTION,
            amount: 0,
            isBold: true,
        );

        $this->assertTrue($section->isSection());
        $this->assertFalse($section->isIncome());
        $this->assertFalse($section->isExpense());
        $this->assertFalse($section->isTotal());
        $this->assertFalse($section->isGrandTotal());
    }

    public function test_report_row_total_type(): void
    {
        $total = new ReportRow(
            date: '',
            category: 'Laba Kotor',
            type: ReportRow::TYPE_TOTAL,
            amount: 120000,
            isBold: true,
        );

        $this->assertTrue($total->isTotal());
        $this->assertFalse($total->isGrandTotal());
    }
}
