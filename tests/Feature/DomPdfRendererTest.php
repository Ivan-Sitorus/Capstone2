<?php

namespace Tests\Feature;

use App\DTO\ReportData;
use App\DTO\ReportRow;
use App\DTO\SummaryItem;
use App\Renderers\DomPdfRenderer;
use Tests\TestCase;

class DomPdfRendererTest extends TestCase
{
    private function makeSampleReportData(): ReportData
    {
        $rows = [];

        // Section header
        $rows[] = new ReportRow(
            date: '', category: 'Laporan Laba Rugi',
            type: ReportRow::TYPE_SECTION, amount: 0, isBold: true,
        );

        // Income rows
        $rows[] = new ReportRow(
            date: '', category: 'Pendapatan Pesanan',
            type: ReportRow::TYPE_INCOME, amount: 1500000,
        );
        $rows[] = new ReportRow(
            date: '', category: 'Pendapatan Tak Terduga',
            type: ReportRow::TYPE_INCOME, amount: 250000, indentLevel: 1,
        );

        // Expense row
        $rows[] = new ReportRow(
            date: '', category: 'HPP',
            type: ReportRow::TYPE_EXPENSE, amount: 800000,
        );

        // Subtotal
        $rows[] = new ReportRow(
            date: '', category: 'Laba Kotor',
            type: ReportRow::TYPE_TOTAL, amount: 700000, isBold: true,
        );

        // Grand total
        $rows[] = new ReportRow(
            date: '', category: 'Laba Rugi Bersih',
            type: ReportRow::TYPE_GRAND_TOTAL, amount: 500000, isBold: true,
        );

        $summary = [
            new SummaryItem(
                label: 'Total Pendapatan', formattedValue: 'Rp 1.750.000',
                rawValue: 1750000,
            ),
            new SummaryItem(
                label: 'Total Pengeluaran', formattedValue: 'Rp 800.000',
                rawValue: 800000,
            ),
            new SummaryItem(
                label: 'Laba Bersih', formattedValue: 'Rp 950.000',
                rawValue: 950000, isHighlighted: true,
            ),
        ];

        return new ReportData(
            type:        ReportData::TYPE_SIMPLE,
            title:       'Laporan Keuangan Januari 2026',
            dateStart:   '2026-01-01',
            dateEnd:     '2026-01-31',
            aggregation: 'daily',
            summary:     $summary,
            rows:        $rows,
        );
    }

    public function test_generates_non_empty_pdf(): void
    {
        $data = $this->makeSampleReportData();
        $pdf = DomPdfRenderer::generate($data);

        $output = $pdf->output();

        $this->assertGreaterThan(0, strlen($output), 'PDF output should not be empty');
        $this->assertStringStartsWith('%PDF-', $output, 'Output should be a valid PDF');
    }

    public function test_pdf_contains_report_title(): void
    {
        $data = $this->makeSampleReportData();
        $pdf = DomPdfRenderer::generate($data);

        $text = $this->extractTextFromPdf($pdf->output());

        $this->assertStringContainsString(
            'W9 Cafe',
            $text,
            'PDF should contain company name',
        );

        $this->assertStringContainsString(
            'Laporan Keuangan Januari 2026',
            $text,
            'PDF should contain report title',
        );

        $this->assertStringContainsString(
            '2026-01-01',
            $text,
            'PDF should contain period start date',
        );
    }

    public function test_pdf_contains_currency_formatting(): void
    {
        $data = $this->makeSampleReportData();
        $pdf = DomPdfRenderer::generate($data);

        $text = $this->extractTextFromPdf($pdf->output());

        $this->assertStringContainsString(
            'Rp',
            $text,
            'PDF should contain Rupiah currency indicator',
        );

        $this->assertStringContainsString(
            'Total Pendapatan',
            $text,
            'PDF should contain summary labels',
        );
    }

    public function test_pdf_with_rigid_report_type(): void
    {
        $data = ReportData::fromRigidReport(
            data: [
                'income_statement' => [
                    'pendapatan'           => 3000000,
                    'pendapatan_orders'    => 2800000,
                    'pendapatan_unexpected' => 200000,
                    'hpp'                  => 1500000,
                    'laba_kotor'           => 1500000,
                    'beban_operasional'    => 600000,
                    'beban_tak_terduga'    => 100000,
                    'laba_rugi_bersih'     => 800000,
                ],
                'cash_flow' => [
                    'arus_kas_masuk'   => 3000000 + 400000,
                    'arus_kas_keluar'  => 1500000 + 600000 + 100000,
                    'arus_kas_bersih'  => 1200000,
                    'receivable_payments' => 400000,
                    'saldo_awal'       => 5000000,
                    'saldo_akhir'      => 6200000,
                ],
                'meta' => [],
            ],
            dateStart: '2026-02-01',
            dateEnd:   '2026-02-28',
        );

        $pdf = DomPdfRenderer::generate($data);

        $this->assertGreaterThan(0, strlen($pdf->output()));
        $this->assertStringStartsWith('%PDF-', $pdf->output());

        $text = $this->extractTextFromPdf($pdf->output());

        $this->assertStringContainsString('Laporan Laba Rugi', $text);
        $this->assertStringContainsString('Laporan Arus Kas', $text);
        $this->assertStringContainsString('Saldo Akhir', $text);
    }

    public function test_raw_method_returns_binary(): void
    {
        $data = $this->makeSampleReportData();
        $raw = DomPdfRenderer::raw($data);

        $this->assertIsString($raw);
        $this->assertGreaterThan(0, strlen($raw));
        $this->assertStringStartsWith('%PDF-', $raw);
    }

    /**
     * Extract readable text from a PDF binary string.
     * Uses pdftotext (poppler-utils) if available; falls back
     * to scanning the raw PDF stream for uncompressed text.
     */
    private function extractTextFromPdf(string $pdfBinary): string
    {
        // Try pdftotext first (poppler-utils)
        $pdftotextPath = trim(shell_exec('which pdftotext 2>/dev/null') ?: '');

        if ($pdftotextPath !== '' && is_executable($pdftotextPath)) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'pdf_test_');
            file_put_contents($tmpFile, $pdfBinary);

            $text = shell_exec(
                sprintf('%s %s - 2>/dev/null', escapeshellcmd($pdftotextPath), escapeshellarg($tmpFile))
            ) ?: '';

            unlink($tmpFile);

            return $text;
        }

        // Fallback: scan raw bytes for text between stream objects
        // DomPDF 3.x with DejaVu Sans uses text encoding in content streams;
        // some text may be extractable from uncompressed stream portions.
        $text = '';

        // Look for uncompressed text in BT/ET blocks
        if (preg_match_all('/\(([^)]{2,})\)/', $pdfBinary, $matches)) {
            $text .= implode(' ', $matches[1]);
        }

        // Also try hexadecimal strings <...>
        if (preg_match_all('/<([0-9A-Fa-f]+)>/', $pdfBinary, $hexMatches)) {
            foreach ($hexMatches[1] as $hex) {
                if (strlen($hex) >= 4 && strlen($hex) % 2 === 0) {
                    $decoded = '';
                    for ($i = 0; $i < strlen($hex); $i += 2) {
                        $byte = hexdec(substr($hex, $i, 2));
                        if ($byte >= 32 && $byte <= 126) {
                            $decoded .= chr($byte);
                        }
                    }
                    if (strlen($decoded) >= 2) {
                        $text .= ' ' . $decoded;
                    }
                }
            }
        }

        return $text;
    }
}
