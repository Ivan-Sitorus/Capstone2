<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;

/**
 * Helper untuk semua input numerik di form Filament.
 * Memakai Alpine $money mask → auto-format ribuan (titik) & desimal (koma).
 * Titik ribuan di-strip sebelum validasi/simpan; koma desimal dinormalisasi ke titik.
 */
class NumericInput
{
    /**
     * Terapkan konfigurasi numeric lengkap ke TextInput:
     * numeric() (keyboard angka + validasi) + type text (agar mask & maxLength jalan)
     * + mask ribuan/desimal + strip titik + batas karakter + normalisasi koma→titik.
     *
     * @param  int  $maxDigits  jumlah digit integer (mis. 6 = max 999.999, 9 = max 999.999.999)
     * @param  int  $precision  digit desimal (0 = integer, 3 = max 3 desimal)
     */
    public static function apply(TextInput $input, int $maxDigits = 6, int $precision = 0): TextInput
    {
        return $input
            ->numeric()
            ->type('text')
            ->mask(static::mask($precision))
            ->stripCharacters('.')
            ->maxLength(static::maxLength($maxDigits, $precision))
            ->mutateStateForValidationUsing(static::validationNormalizer())
            ->dehydrateStateUsing(fn ($state) => static::normalizeState($state));
    }

    /**
     * Mask Alpine $money dengan format Indonesia:
     * - ribuan separator: '.'  (muncul otomatis, mis. 15000 → 15.000)
     * - desimal separator: ',' (koma)
     * - precision: jumlah digit desimal (0 = integer, 3 = max 3 desimal)
     */
    public static function mask(int $precision = 0): RawJs
    {
        return RawJs::make("\$money(\$input, ',', '.', {$precision})");
    }

    /**
     * Maksimal karakter input (termasuk titik ribuan & koma desimal).
     * Contoh: 6 digit + 0 desimal → "999.999" = 7 char; 6 digit + 3 desimal → "999.999,999" = 11 char.
     */
    public static function maxLength(int $maxDigits = 6, int $precision = 0): int
    {
        $thousandDots = (int) floor(($maxDigits - 1) / 3);
        $decimalPart = $precision > 0 ? 1 + $precision : 0;

        return $maxDigits + $thousandDots + $decimalPart;
    }

    /**
     * Koma desimal (format Indonesia) → titik untuk validasi numeric & DB.
     */
    public static function normalizeState(mixed $state): mixed
    {
        return is_string($state) ? str_replace(',', '.', $state) : $state;
    }

    /**
     * Closure normalisasi koma→titik SEBELUM validasi (mutateStateForValidationUsing).
     * Tanpa ini, input "8,8" gagal rule numeric karena koma belum dinormalisasi.
     */
    public static function validationNormalizer(): \Closure
    {
        return fn (mixed $state): mixed => static::normalizeState($state);
    }
}
