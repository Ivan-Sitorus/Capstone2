<?php

namespace Tests\Feature;

use App\DTO\ReportData;
use App\DTO\ReportRow;
use App\DTO\SummaryItem;
use App\Renderers\CsvRenderer;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class CsvRendererTest extends TestCase
{
    private ReportData $reportData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reportData = new ReportData(
            type: ReportData::TYPE_SIMPLE,
            title: 'Test Report',
            dateStart: '2026-05-01',
            dateEnd: '2026-05-31',
            aggregation: 'daily',
            summary: [
                new SummaryItem('Total Pendapatan', 'Rp 150.000', 150000),
                new SummaryItem('Total Pengeluaran', 'Rp 50.000', 50000),
                new SummaryItem('Net', 'Rp 100.000', 100000, true),
            ],
            rows: [
                new ReportRow('2026-05-01', 'Kopi Robusta', ReportRow::TYPE_INCOME, 75000),
                new ReportRow('2026-05-02', 'Roti Bakar', ReportRow::TYPE_INCOME, 75000),
                new ReportRow('2026-05-03', 'Bahan Baku', ReportRow::TYPE_EXPENSE, 30000),
                new ReportRow('2026-05-04', 'Operasional', ReportRow::TYPE_EXPENSE, 20000),
            ],
        );
    }

    public function test_generates_valid_csv(): void
    {
        $response = CsvRenderer::download($this->reportData, 'laporan.csv');

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/csv; charset=utf-8', $response->headers->get('Content-Type'));
        $this->assertSame('attachment; filename="laporan.csv"', $response->headers->get('Content-Disposition'));
    }

    public function test_csv_contains_header_row(): void
    {
        $content = CsvRenderer::toCsvString($this->reportData);

        $lines = explode("\n", trim($content));
        $this->assertStringContainsString('Tanggal', $lines[0]);
        $this->assertStringContainsString('Kategori', $lines[0]);
        $this->assertStringContainsString('Tipe', $lines[0]);
        $this->assertStringContainsString('Jumlah', $lines[0]);
    }

    public function test_csv_has_utf8_bom(): void
    {
        $content = CsvRenderer::toCsvString($this->reportData);

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
    }

    public function test_csv_contains_data_rows(): void
    {
        $content = CsvRenderer::toCsvString($this->reportData);

        $lines = array_filter(explode("\n", $content));
        // 1 header + 4 data rows = 5 lines
        $this->assertCount(5, $lines);
    }

    public function test_csv_contains_expected_data_values(): void
    {
        $content = CsvRenderer::toCsvString($this->reportData);

        $this->assertStringContainsString('2026-05-01', $content);
        $this->assertStringContainsString('Kopi Robusta', $content);
        $this->assertStringContainsString('Income', $content);
        $this->assertStringContainsString('75000', $content);
    }

    public function test_csv_amounts_are_raw_numbers_not_formatted(): void
    {
        $content = CsvRenderer::toCsvString($this->reportData);

        $this->assertStringNotContainsString('Rp', $content);
        // Amounts appear as raw numbers like 75000 not "75.000"
        $this->assertStringContainsString('75000', $content);
        $this->assertStringContainsString('30000', $content);
    }

    public function test_csv_uses_default_filename(): void
    {
        $response = CsvRenderer::download($this->reportData);

        $disposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('report.csv', $disposition);
    }

    public function test_csv_empty_rows_still_has_header(): void
    {
        $emptyData = new ReportData(
            type: ReportData::TYPE_SIMPLE,
            title: 'Empty Report',
            dateStart: '2026-05-01',
            dateEnd: '2026-05-31',
            aggregation: 'daily',
            rows: [],
        );

        $content = CsvRenderer::toCsvString($emptyData);

        $lines = array_filter(explode("\n", $content));
        $this->assertCount(1, $lines);
        $this->assertStringContainsString('Tanggal', $content);
    }
}
