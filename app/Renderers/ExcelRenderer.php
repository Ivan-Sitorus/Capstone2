<?php

namespace App\Renderers;

use App\DTO\ReportData;
use App\DTO\ReportRow;
use App\Helpers\AccountingFormatter;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelRenderer implements
    FromArray,
    WithHeadings,
    WithStyles,
    WithColumnWidths,
    WithColumnFormatting,
    WithEvents,
    ShouldAutoSize,
    WithTitle
{
    /**
     * @param  ReportData  $reportData  The DTO containing report rows and metadata.
     */
    public function __construct(
        private readonly ReportData $reportData,
    ) {
    }

    // ─── Maatwebsite Excel Concerns ──────────────────────────────────────

    /**
     * Return the data rows as an array of arrays.
     * Each inner array represents one Excel row.
     */
    public function array(): array
    {
        $data = [];

        foreach ($this->reportData->rows as $row) {
            $data[] = [
                $row->date ?: '',
                $this->indentCategory($row),
                $this->translateType($row->type),
                $row->amount,
                $row->runningTotal,
            ];
        }

        return $data;
    }

    /**
     * Column headers displayed at row 5 (after 4 title rows are inserted).
     */
    public function headings(): array
    {
        return [
            'Tanggal',
            'Kategori',
            'Tipe',
            'Jumlah',
            'Saldo Berjalan',
        ];
    }

    /**
     * Overall worksheet styles for the default cell style.
     */
    public function styles(Worksheet $sheet): void
    {
        $sheet->getParentOrThrow()->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);
    }

    /**
     * Hard-coded column widths (ShouldAutoSize will further refine).
     */
    public function columnWidths(): array
    {
        return [
            'A' => 16,   // Tanggal
            'B' => 38,   // Kategori
            'C' => 13,   // Tipe
            'D' => 22,   // Jumlah
            'E' => 22,   // Saldo Berjalan
        ];
    }

    /**
     * Apply Rupiah accounting format to amount columns.
     */
    public function columnFormats(): array
    {
        $fmt = AccountingFormatter::excelFormatRupiah();

        return [
            'D' => $fmt,
            'E' => $fmt,
        ];
    }

    /**
     * Sheet tab title.
     */
    public function title(): string
    {
        $typeLabel = match ($this->reportData->type) {
            ReportData::TYPE_SIMPLE => 'Simple',
            ReportData::TYPE_RIGID  => 'Rigid',
            ReportData::TYPE_CUSTOM => 'Custom',
            default                 => 'Report',
        };

        return "{$typeLabel} Report";
    }

    /**
     * Register AfterSheet event for:
     *  - Inserting title/metadata rows at top
     *  - Per-row styling (section, total, grand-total)
     *  - Freeze pane, page setup, gridlines, print settings
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->afterSheet($event);
            },
        ];
    }

    // ─── AfterSheet Logic ──────────────────────────────────────────────

    private function afterSheet(AfterSheet $event): void
    {
        $sheet   = $event->sheet->getDelegate();
        $lastCol = 'E';

        // ── 1. Insert 4 title rows at top, shifting headers+data down ──
        $sheet->insertNewRowBefore(1, 4);

        // ── 2. Row 1: Company name ─────────────────────────────────────
        $sheet->setCellValue('A1', 'W9 Cafe');
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F4E79']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // ── 3. Row 2: Report title ─────────────────────────────────────
        $sheet->setCellValue('A2', $this->reportData->title);
        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // ── 4. Row 3: Period ───────────────────────────────────────────
        $periodLabel = $this->buildPeriodLabel();
        $sheet->setCellValue('A3', $periodLabel);
        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '808080']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // ── 5. Row 4: Spacer ───────────────────────────────────────────
        $sheet->getRowDimension(4)->setRowHeight(6);

        // ── 6. Row 5: Column headers (navy background, white text) ─────
        $headerRow = 5;
        $headerRange = "A{$headerRow}:{$lastCol}{$headerRow}";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(20);

        // ── 7. Data rows: per-row styling (row 6 onward) ───────────────
        $dataStartRow = 6;
        $rows = $this->reportData->rows;
        $count = count($rows);

        for ($i = 0; $i < $count; $i++) {
            $excelRow  = $dataStartRow + $i;
            $row       = $rows[$i];
            $cellRange = "A{$excelRow}:{$lastCol}{$excelRow}";

            $isAlternating = ($i % 2 === 0);

            if ($row->isSection()) {
                // Section header rows
                $sheet->getStyle($cellRange)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1F4E79']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D6E4F0']],
                ]);
            } elseif ($row->isGrandTotal()) {
                // Grand total rows
                $sheet->getStyle($cellRange)->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 11],
                    'borders' => ['top' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '1F4E79']]],
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']],
                ]);
            } elseif ($row->isTotal()) {
                // Sub-total rows
                $sheet->getStyle($cellRange)->applyFromArray([
                    'font'    => ['bold' => true, 'size' => 11],
                    'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1F4E79']]],
                ]);

                if ($isAlternating) {
                    $sheet->getStyle($cellRange)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->setStartColor(new \PhpOffice\PhpSpreadsheet\Style\Color('F5F7FA'));
                }
            } elseif ($row->isBold) {
                // Rows marked as bold only
                $sheet->getStyle($cellRange)->getFont()->setBold(true);

                if ($isAlternating) {
                    $sheet->getStyle($cellRange)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->setStartColor(new \PhpOffice\PhpSpreadsheet\Style\Color('F5F7FA'));
                }
            } elseif ($isAlternating) {
                // Alternating clean rows
                $sheet->getStyle($cellRange)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F7FA']],
                ]);
            }
        }

        // ── 8. Alignment for amount & running-total columns ────────────
        $dataEndRow = $dataStartRow + $count - 1;
        if ($count > 0) {
            $sheet->getStyle("D{$dataStartRow}:{$lastCol}{$dataEndRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Center-align the Date, Category, Type columns
            $sheet->getStyle("A{$dataStartRow}:C{$dataEndRow}")
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER);
        }

        // ── 9. Freeze pane below header (A6) ───────────────────────────
        $sheet->freezePane('A6');

        // ── 10. Hide gridlines ─────────────────────────────────────────
        $sheet->setShowGridlines(false);

        // ── 11. Page Setup: landscape A4, fit to 1 page wide ───────────
        $pageSetup = $sheet->getPageSetup();
        $pageSetup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $pageSetup->setPaperSize(PageSetup::PAPERSIZE_A4);
        $pageSetup->setFitToWidth(1);
        $pageSetup->setFitToHeight(0);

        // ── 12. Print margins ──────────────────────────────────────────
        $sheet->getPageMargins()
            ->setTop(0.5)
            ->setBottom(0.5)
            ->setLeft(0.5)
            ->setRight(0.5);

        // ── 13. Print header / footer ──────────────────────────────────
        $sheet->getHeaderFooter()
            ->setOddHeader('&C&"Calibri,Bold"&14 W9 Cafe — Laporan Keuangan')
            ->setOddFooter('&L&Dibuat: &D &T&RPage &P of &N');
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    /**
     * Indent category text based on indentLevel.
     */
    private function indentCategory(ReportRow $row): string
    {
        $indent = str_repeat('  ', max(0, $row->indentLevel));

        return $indent . $row->category;
    }

    /**
     * Translate internal type codes to Indonesian labels.
     */
    private function translateType(string $type): string
    {
        return match ($type) {
            ReportRow::TYPE_INCOME      => 'Pemasukan',
            ReportRow::TYPE_EXPENSE     => 'Pengeluaran',
            ReportRow::TYPE_SECTION     => 'Bagian',
            ReportRow::TYPE_TOTAL       => 'Subtotal',
            ReportRow::TYPE_GRAND_TOTAL => 'Total',
            default                     => $type,
        };
    }

    /**
     * Build a human-readable period label from the DTO dates.
     */
    private function buildPeriodLabel(): string
    {
        if ($this->reportData->dateStart && $this->reportData->dateEnd) {
            $start = AccountingFormatter::dateIndo($this->reportData->dateStart);
            $end   = AccountingFormatter::dateIndo($this->reportData->dateEnd);

            return "Periode: {$start} — {$end}";
        }

        if ($this->reportData->dateStart) {
            return 'Periode: ' . AccountingFormatter::dateIndo($this->reportData->dateStart);
        }

        return 'Periode: —';
    }
}
