<?php

namespace App\Support;

/**
 * Centralised id-ID number and currency formatting.
 *
 * The canonical rupiah output is "Rp 45.000": the "Rp " prefix (with a
 * space), zero decimals and dot thousands separator, matching the existing
 * admin UI output and resources/js formatRupiah().
 */
final class Formatter
{
    /**
     * Format an amount as Indonesian rupiah, e.g. 45000 => "Rp 45.000".
     */
    public static function rupiah(int|float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Format a plain number using id-ID grouping, e.g. 45000 => "45.000".
     */
    public static function number(int|float $value, int $decimals = 0): string
    {
        return number_format($value, $decimals, ',', '.');
    }
}
