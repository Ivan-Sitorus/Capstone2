<?php

namespace App\Renderers;

use App\DTO\ReportData;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvRenderer
{
    public static function download(ReportData $data, string $filename = 'report.csv'): StreamedResponse
    {
        $callback = function () use ($data) {
            echo self::toCsvString($data);
        };

        return new StreamedResponse($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public static function toCsvString(ReportData $data): string
    {
        $file = fopen('php://temp', 'r+');

        // UTF-8 BOM for Indonesian Excel compatibility
        fwrite($file, "\xEF\xBB\xBF");

        fputcsv($file, ['Tanggal', 'Kategori', 'Tipe', 'Jumlah']);

        foreach ($data->rows as $row) {
            fputcsv($file, [
                $row->date,
                $row->category,
                $row->type,
                $row->amount,
            ]);
        }

        rewind($file);
        $content = stream_get_contents($file);
        fclose($file);

        return $content;
    }
}
