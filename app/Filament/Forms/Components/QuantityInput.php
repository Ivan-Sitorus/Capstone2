<?php

namespace App\Filament\Forms\Components;

class QuantityInput
{
    /**
     * Blokir karakter non-numerik di level ketikan.
     * Cegah "1e309" → INF/NaN crash saat di-cast ke float (field live).
     * Hanya digit & koma (separator desimal), max 1 koma, max 10 karakter.
     *
     * @param  int  $maxDecimals  maksimal digit desimal setelah koma (default 3, sesuai presisi DB)
     */
    public static function inputAttributes(int $maxDecimals = 3): array
    {
        return [
            'oninput' => "this.value = this.value.replace(/[^0-9,]/g, '').replace(/(,.*),/g, '\$1').replace(/(,\d{0," . $maxDecimals . "}).*/, '\$1').slice(0, 10)",
        ];
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
