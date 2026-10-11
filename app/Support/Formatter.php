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

    /**
     * Format a ratio as an id-ID percentage, e.g. 0.1234 => "12,34%".
     *
     * Pass the raw ratio (0..1); the value is multiplied by 100 here.
     */
    public static function percent(float $ratio, int $decimals = 2): string
    {
        return number_format($ratio * 100, $decimals, ',', '.') . '%';
    }
}
