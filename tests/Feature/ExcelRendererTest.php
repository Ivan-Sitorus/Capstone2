<?php

namespace Tests\Feature;

use App\DTO\ReportData;
use App\DTO\ReportRow;
use App\Helpers\AccountingFormatter;
use App\Renderers\ExcelRenderer;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Tests\TestCase;

class ExcelRendererTest extends TestCase
{
    private ReportData $rigidReportData;

    private ReportData $simpleReportData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rigidReportData = $this->makeRigidReportData();
        $this->simpleReportData = $this->makeSimpleReportData();
    }

    // ─── Basic generation ───────────────────────────────────────────────

    public function test_generates_xlsx_file(): void
    {
        $path = 'test_export.xlsx';

        Excel::store(new ExcelRenderer($this->rigidReportData), $path);

        $fullPath = storage_path("app/{$path}");
        $this->assertFileExists($fullPath);

        $spreadsheet = IOFactory::load($fullPath);
        $this->assertNotNull($spreadsheet);

        $sheet = $spreadsheet->getActiveSheet();
        $this->assertNotNull($sheet);

        unlink($fullPath);
    }

    public function test_sheet_has_correct_title(): void
    {
        $path = 'test_title.xlsx';
        Excel::store(new ExcelRenderer($this->rigidReportData), $path);

        $spreadsheet = IOFactory::load(storage_path("app/{$path}"));
        $this->assertStringContainsString('Rigid', $spreadsheet->getActiveSheet()->getTitle());

        unlink(storage_path("app/{$path}"));
    }

    // ─── Title rows ────────────────────────────────────────────────────

    public function test_company_name_row_is_merged_and_styled(): void
    {
        $path = 'test_company.xlsx';
        Excel::store(new ExcelRenderer($this->rigidReportData), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();

        $this->assertEquals('W9 Cafe', $sheet->getCell('A1')->getValue());
        $this->assertTrue($sheet->getCell('A1')->getStyle()->getFont()->getBold());
        $this->assertEquals(14, $sheet->getCell('A1')->getStyle()->getFont()->getSize());
        $this->assertEquals('1F4E79', $sheet->getCell('A1')->getStyle()->getFont()->getColor()->getRGB());

        unlink(storage_path("app/{$path}"));
    }

    public function test_report_title_row_is_merged_and_bold(): void
    {
        $path = 'test_title_row.xlsx';
        Excel::store(new ExcelRenderer($this->rigidReportData), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();

        $this->assertEquals($this->rigidReportData->title, $sheet->getCell('A2')->getValue());
        $this->assertTrue($sheet->getCell('A2')->getStyle()->getFont()->getBold());
        $this->assertEquals(13, $sheet->getCell('A2')->getStyle()->getFont()->getSize());

        unlink(storage_path("app/{$path}"));
    }

    public function test_period_row_is_italic_and_gray(): void
    {
        $path = 'test_period.xlsx';
        Excel::store(new ExcelRenderer($this->rigidReportData), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();

        $periodValue = $sheet->getCell('A3')->getValue();
        $this->assertStringContainsString('Periode:', $periodValue);
        $this->assertTrue($sheet->getCell('A3')->getStyle()->getFont()->getItalic());
        $this->assertEquals('808080', $sheet->getCell('A3')->getStyle()->getFont()->getColor()->getRGB());

        unlink(storage_path("app/{$path}"));
    }

    // ─── Header row ────────────────────────────────────────────────────

    public function test_header_row_has_navy_background(): void
    {
        $path = 'test_header.xlsx';
        Excel::store(new ExcelRenderer($this->rigidReportData), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();

        $headerStyle = $sheet->getStyle('A5:E5');
        $fill = $headerStyle->getFill();

        $this->assertEquals(Fill::FILL_SOLID, $fill->getFillType());
        $this->assertEquals('1F4E79', $fill->getStartColor()->getRGB());

        $this->assertTrue($headerStyle->getFont()->getBold());
        $this->assertEquals('FFFFFF', $headerStyle->getFont()->getColor()->getRGB());

        $this->assertEquals(
            Alignment::HORIZONTAL_CENTER,
            $headerStyle->getAlignment()->getHorizontal()
        );

        unlink(storage_path("app/{$path}"));
    }

    public function test_header_row_has_correct_labels(): void
    {
        $path = 'test_header_labels.xlsx';
        Excel::store(new ExcelRenderer($this->rigidReportData), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();

        $this->assertEquals('Tanggal', $sheet->getCell('A5')->getValue());
        $this->assertEquals('Kategori', $sheet->getCell('B5')->getValue());
        $this->assertEquals('Tipe', $sheet->getCell('C5')->getValue());
        $this->assertEquals('Jumlah', $sheet->getCell('D5')->getValue());
        $this->assertEquals('Saldo Berjalan', $sheet->getCell('E5')->getValue());

        unlink(storage_path("app/{$path}"));
    }

    // ─── Section rows ──────────────────────────────────────────────────

    public function test_section_rows_have_light_blue_background(): void
    {
        $path = 'test_sections.xlsx';
        Excel::store(new ExcelRenderer($this->rigidReportData), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();

        $sectionRow = $this->findSectionRow($sheet);
        $this->assertNotNull($sectionRow, 'No section row found');

        $style = $sheet->getStyle("A{$sectionRow}:E{$sectionRow}");
        $this->assertEquals('D6E4F0', $style->getFill()->getStartColor()->getRGB());
        $this->assertTrue($style->getFont()->getBold());
        $this->assertEquals('1F4E79', $style->getFont()->getColor()->getRGB());

        unlink(storage_path("app/{$path}"));
    }

    // ─── Total / Grand total rows ──────────────────────────────────────

    public function test_total_rows_have_bold_and_medium_border(): void
    {
        $path = 'test_total_border.xlsx';
        Excel::store(new ExcelRenderer($this->rigidReportData), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();

        $totalRow = $this->findTotalRow($sheet);
        $this->assertNotNull($totalRow, 'No total row found');

        $style = $sheet->getStyle("A{$totalRow}:E{$totalRow}");
        $this->assertTrue($style->getFont()->getBold());

        $topBorder = $style->getBorders()->getTop();
        $this->assertEquals(Border::BORDER_MEDIUM, $topBorder->getBorderStyle());

        unlink(storage_path("app/{$path}"));
    }

    public function test_grand_total_rows_have_double_border_and_green_background(): void
    {
        $path = 'test_grand_total.xlsx';
        Excel::store(new ExcelRenderer($this->rigidReportData), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();

        $grandTotalRow = $this->findGrandTotalRow($sheet);
        $this->assertNotNull($grandTotalRow, 'No grand total row found');

        $style = $sheet->getStyle("A{$grandTotalRow}:E{$grandTotalRow}");
        $this->assertTrue($style->getFont()->getBold());
        $this->assertEquals('E2EFDA', $style->getFill()->getStartColor()->getRGB());

        $topBorder = $style->getBorders()->getTop();
        $this->assertEquals(Border::BORDER_DOUBLE, $topBorder->getBorderStyle());

        unlink(storage_path("app/{$path}"));
    }

    // ─── Currency format ───────────────────────────────────────────────

    public function test_currency_columns_use_accounting_format(): void
    {
        $path = 'test_currency.xlsx';
        $data = $this->makeSimpleIncomeOnlyReport();
        Excel::store(new ExcelRenderer($data), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();

        $expectedFormat = AccountingFormatter::excelFormatRupiah();

        $this->assertEquals(
            $expectedFormat,
            $sheet->getStyle('D6')->getNumberFormat()->getFormatCode()
        );
        $this->assertEquals(
            $expectedFormat,
            $sheet->getStyle('E6')->getNumberFormat()->getFormatCode()
        );

        unlink(storage_path("app/{$path}"));
    }

    public function test_amount_columns_are_right_aligned(): void
    {
        $path = 'test_alignment.xlsx';
        $data = $this->makeSimpleIncomeOnlyReport();
        Excel::store(new ExcelRenderer($data), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();

        $this->assertEquals(
            Alignment::HORIZONTAL_RIGHT,
            $sheet->getStyle('D6')->getAlignment()->getHorizontal()
        );

        unlink(storage_path("app/{$path}"));
    }

    // ─── Page setup & freeze pane ──────────────────────────────────────

    public function test_freeze_pane_is_set_below_header(): void
    {
        $path = 'test_freeze.xlsx';
        Excel::store(new ExcelRenderer($this->rigidReportData), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();

        $this->assertEquals('A6', $sheet->getFreezePane());

        unlink(storage_path("app/{$path}"));
    }

    public function test_gridlines_are_hidden(): void
    {
        $path = 'test_gridlines.xlsx';
        Excel::store(new ExcelRenderer($this->rigidReportData), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();

        $this->assertFalse($sheet->getShowGridlines());

        unlink(storage_path("app/{$path}"));
    }

    public function test_page_setup_is_landscape_a4(): void
    {
        $path = 'test_page_setup.xlsx';
        Excel::store(new ExcelRenderer($this->rigidReportData), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();
        $pageSetup = $sheet->getPageSetup();

        $this->assertEquals('landscape', $pageSetup->getOrientation());
        $this->assertEquals(9, $pageSetup->getPaperSize()); // A4 = 9
        $this->assertEquals(1, $pageSetup->getFitToWidth());
        $this->assertEquals(0, $pageSetup->getFitToHeight());

        unlink(storage_path("app/{$path}"));
    }

    // ─── Data content ──────────────────────────────────────────────────

    public function test_data_rows_contain_category_and_amount(): void
    {
        $path = 'test_data_content.xlsx';
        Excel::store(new ExcelRenderer($this->simpleReportData), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();

        $foundIncome = false;
        $foundExpense = false;
        $highestRow = $sheet->getHighestRow();

        for ($row = 6; $row <= $highestRow; $row++) {
            $type = $sheet->getCell("C{$row}")->getValue();
            $amount = $sheet->getCell("D{$row}")->getValue();

            if ($type === 'Pemasukan' && is_numeric($amount) && $amount > 0) {
                $foundIncome = true;
            }
            if ($type === 'Pengeluaran' && is_numeric($amount) && $amount > 0) {
                $foundExpense = true;
            }
        }

        $this->assertTrue($foundIncome, 'Should have at least one income row');
        $this->assertTrue($foundExpense, 'Should have at least one expense row');

        unlink(storage_path("app/{$path}"));
    }

    public function test_sheet_contains_all_rows_from_dto(): void
    {
        $path = 'test_all_rows.xlsx';
        $data = $this->rigidReportData;
        Excel::store(new ExcelRenderer($data), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();

        $expectedDataRowCount = count($data->rows);
        $actualDataRowCount = $sheet->getHighestRow() - 5;

        $this->assertEquals(
            $expectedDataRowCount,
            $actualDataRowCount,
            "Expected {$expectedDataRowCount} data rows, got {$actualDataRowCount}"
        );

        unlink(storage_path("app/{$path}"));
    }

    // ─── Alternating rows ──────────────────────────────────────────────

    public function test_alternating_rows_have_different_backgrounds(): void
    {
        $path = 'test_alternating.xlsx';
        $data = $this->makeUniformIncomeRows(5);
        Excel::store(new ExcelRenderer($data), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();

        $evenFill = $sheet->getStyle('A6:E6')->getFill()->getStartColor()->getRGB();
        $oddFill = $sheet->getStyle('A7:E7')->getFill()->getStartColor()->getRGB();

        $this->assertNotEquals(
            $evenFill,
            $oddFill,
            'Even and odd rows should have different fill colors'
        );

        unlink(storage_path("app/{$path}"));
    }

    // ─── Empty report edge case ────────────────────────────────────────

    public function test_empty_report_does_not_throw(): void
    {
        $data = new ReportData(
            type: ReportData::TYPE_SIMPLE,
            title: 'Empty Report',
            dateStart: '2026-01-01',
            dateEnd: '2026-01-31',
            aggregation: 'daily',
            summary: [],
            rows: [],
        );

        $path = 'test_empty.xlsx';
        Excel::store(new ExcelRenderer($data), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();

        $this->assertNotNull($sheet);
        $this->assertEquals('W9 Cafe', $sheet->getCell('A1')->getValue());

        unlink(storage_path("app/{$path}"));
    }

    public function test_indentation_is_applied_to_category(): void
    {
        $data = new ReportData(
            type: ReportData::TYPE_CUSTOM,
            title: 'Indent Test',
            dateStart: '2026-03-01',
            dateEnd: '2026-03-31',
            aggregation: 'monthly',
            summary: [],
            rows: [
                new ReportRow(
                    date: '',
                    category: 'Parent Item',
                    type: ReportRow::TYPE_INCOME,
                    amount: 100000,
                    indentLevel: 0,
                ),
                new ReportRow(
                    date: '',
                    category: 'Child Item',
                    type: ReportRow::TYPE_INCOME,
                    amount: 50000,
                    indentLevel: 1,
                ),
            ],
        );

        $path = 'test_indent.xlsx';
        Excel::store(new ExcelRenderer($data), $path);

        $sheet = IOFactory::load(storage_path("app/{$path}"))->getActiveSheet();

        $this->assertEquals('Parent Item', $sheet->getCell('B6')->getValue());
        $this->assertEquals('  Child Item', $sheet->getCell('B7')->getValue());

        unlink(storage_path("app/{$path}"));
    }

    // ─── Helpers ───────────────────────────────────────────────────────

    private function makeRigidReportData(): ReportData
    {
        return ReportData::fromRigidReport([
            'income_statement' => [
                'pendapatan' => 5000000,
                'pendapatan_orders' => 4500000,
                'pendapatan_unexpected' => 500000,
                'hpp' => 2000000,
                'laba_kotor' => 3000000,
                'beban_operasional' => 1500000,
                'beban_tak_terduga' => 200000,
                'laba_rugi_bersih' => 1300000,
            ],
            'cash_flow' => [
                'arus_kas_masuk' => 5000000,
                'arus_kas_keluar' => 3700000,
                'arus_kas_bersih' => 1300000,
                'receivable_payments' => 500000,
                'saldo_awal' => 2000000,
                'saldo_akhir' => 3300000,
            ],
            'meta' => [],
        ], '2026-05-01', '2026-05-07');
    }

    private function makeSimpleReportData(): ReportData
    {
        return ReportData::fromSimpleReport([
            'income_breakdown' => [
                ['source' => 'Pesanan QRIS', 'total' => 3000000],
                ['source' => 'Pesanan Tunai', 'total' => 1500000],
            ],
            'expense_breakdown' => [
                ['source' => 'Bahan Baku', 'total' => 1200000],
                ['source' => 'Operasional', 'total' => 500000],
            ],
            'total_income' => 4500000,
            'total_expense' => 1700000,
            'net' => 2800000,
        ], '2026-05-01', '2026-05-07');
    }

    private function makeSimpleIncomeOnlyReport(): ReportData
    {
        return new ReportData(
            type: ReportData::TYPE_SIMPLE,
            title: 'Income Only',
            dateStart: '2026-05-01',
            dateEnd: '2026-05-07',
            aggregation: 'daily',
            summary: [],
            rows: [
                new ReportRow(
                    date: '2026-05-01',
                    category: 'Pesanan QRIS',
                    type: ReportRow::TYPE_INCOME,
                    amount: 5000000,
                ),
            ],
        );
    }

    private function makeUniformIncomeRows(int $count): ReportData
    {
        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $rows[] = new ReportRow(
                date: '2026-05-0'.($i + 1),
                category: "Item {$i}",
                type: ReportRow::TYPE_INCOME,
                amount: 100000 * ($i + 1),
            );
        }

        return new ReportData(
            type: ReportData::TYPE_SIMPLE,
            title: 'Alternating Test',
            dateStart: '2026-05-01',
            dateEnd: '2026-05-07',
            aggregation: 'daily',
            summary: [],
            rows: $rows,
        );
    }

    private function findSectionRow(Worksheet $sheet): ?int
    {
        for ($row = 6; $row <= $sheet->getHighestRow(); $row++) {
            if ($sheet->getCell("C{$row}")->getValue() === 'Bagian') {
                return $row;
            }
        }

        return null;
    }

    private function findTotalRow(Worksheet $sheet): ?int
    {
        for ($row = 6; $row <= $sheet->getHighestRow(); $row++) {
            if ($sheet->getCell("C{$row}")->getValue() === 'Subtotal') {
                return $row;
            }
        }

        return null;
    }

    private function findGrandTotalRow(Worksheet $sheet): ?int
    {
        for ($row = 6; $row <= $sheet->getHighestRow(); $row++) {
            if ($sheet->getCell("C{$row}")->getValue() === 'Total') {
                return $row;
            }
        }

        return null;
    }
}
