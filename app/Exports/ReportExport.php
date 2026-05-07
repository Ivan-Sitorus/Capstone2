<?php

namespace App\Exports;

use App\Models\GeneratedReport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements FromArray, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles, WithStrictNullComparison
{
    public function __construct(
        protected GeneratedReport $report,
        protected array $summary,
        protected array $rows,
    ) {}

    public function title(): string
    {
        return 'Report ' . $this->report->id;
    }

    public function headings(): array
    {
        $headings = ['Date', 'Category', 'Type', 'Amount'];
        if (! empty($this->rows) && isset($this->rows[0]['running_total'])) {
            $headings[] = 'Running Total';
        }
        return $headings;
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function map($row): array
    {
        $mapped = [
            $row['date'] ?? '-',
            $row['category'] ?? '-',
            $row['type'] ?? '-',
            (float) ($row['amount'] ?? 0),
        ];
        if (isset($row['running_total'])) {
            $mapped[] = (float) $row['running_total'];
        }
        return $mapped;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
