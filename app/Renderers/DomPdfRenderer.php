<?php

namespace App\Renderers;

use App\DTO\ReportData;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDFInstance;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DomPdfRenderer
{
    /**
     * Generate a DomPDF instance from a ReportData DTO.
     *
     * @param  ReportData  $data  The report data to render
     * @param  array  $options  PDF options:
     *                          - 'format'      => 'A4' (default), 'A3', 'Letter', 'Legal'
     *                          - 'orientation' => 'landscape' (default), 'portrait'
     *                          - 'paper_width'  => custom width in mm (overrides format)
     *                          - 'paper_height' => custom height in mm (overrides format)
     */
    public static function generate(ReportData $data, array $options = []): DomPDFInstance
    {
        $html = view('pdfs.financial-report', [
            'reportData' => $data,
            'summary' => $data->summary,
            'rows' => $data->rows,
        ])->render();

        $pdf = Pdf::loadHTML($html);

        $format = $options['format'] ?? 'A4';
        $orientation = $options['orientation'] ?? 'landscape';

        if (isset($options['paper_width'], $options['paper_height'])) {
            $pdf->setPaper(
                (float) $options['paper_width'],
                (float) $options['paper_height'],
            );
        } else {
            $pdf->setPaper($format, $orientation);
        }

        return $pdf;
    }

    /**
     * Generate a PDF and trigger a browser download.
     *
     * @param  ReportData  $data  The report data to render
     * @param  array  $options  PDF options (see generate()) plus:
     *                          - 'filename' => 'report.pdf' (default)
     */
    public static function download(ReportData $data, array $options = []): StreamedResponse
    {
        $pdf = self::generate($data, $options);
        $filename = $options['filename'] ?? 'report.pdf';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf'],
        );
    }

    /**
     * Generate a PDF and return the raw binary content.
     * Useful for saving to storage or attaching to emails.
     *
     * @param  array  $options  PDF options (see generate())
     * @return string Raw PDF binary
     */
    public static function raw(ReportData $data, array $options = []): string
    {
        $pdf = self::generate($data, $options);

        return $pdf->output();
    }
}
