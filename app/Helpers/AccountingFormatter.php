<?php

namespace App\Helpers;

use Carbon\Carbon;

final class AccountingFormatter
{
    /**
     * Private constructor to prevent instantiation.
     */
    private function __construct() {}

    /**
     * Format angka ke Rupiah Indonesia: "Rp 1.000.000".
     *
     * @param  float  $amount
     * @return string
     */
    public static function rupiah(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Format angka untuk UI display (bisa negatif di-wrap parentheses).
     *
     * @param  float  $amount
     * @return string
     */
    public static function rupiahAccounting(float $amount): string
    {
        if ($amount < 0) {
            return '(Rp ' . number_format(abs($amount), 0, ',', '.') . ')';
        }
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Return PhpSpreadsheet accounting format string for IDR.
     *
     * @return string
     */
    public static function excelFormatRupiah(): string
    {
        return '_(Rp* #,##0_);_(Rp* (#,##0);_(Rp* "-"??_);_(@_)';
    }

    /**
     * Return percentage format for PhpSpreadsheet.
     *
     * @return string
     */
    public static function excelPercentFormat(): string
    {
        return '0.00%';
    }

    /**
     * Format tanggal ke Indonesia: "22 Feb 2026".
     *
     * @param  string  $date
     * @return string
     */
    public static function dateIndo(string $date): string
    {
        return Carbon::parse($date)->format('d M Y');
    }

    /**
     * Format tanggal lengkap: "22 Februari 2026".
     *
     * @param  string  $date
     * @return string
     */
    public static function dateIndoFull(string $date): string
    {
        $previousLocale = Carbon::getLocale();
        Carbon::setLocale('id');
        $result = Carbon::parse($date)->isoFormat('D MMMM Y');
        Carbon::setLocale($previousLocale);

        return $result;
    }

    /**
     * Map internal source codes to Indonesian labels.
     *
     * @param  string  $source
     * @return string
     */
    public static function sourceLabel(string $source): string
    {
        return match ($source) {
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            'transfer' => 'Transfer',
            'card' => 'Kartu',
            'bayar_nanti' => 'Bayar Nanti',
            'unexpected_income' => 'Pemasukan Tidak Terduga',
            'ingredient_purchase' => 'Pembelian Bahan Baku',
            'unexpected_expense' => 'Pengeluaran Tidak Terduga',
            default => ucfirst(str_replace('_', ' ', $source)),
        };
    }
}
