<?php

namespace App\Filament\Forms\Components;

class QuantityInput
{
    /**
     * Blokir karakter non-numerik di level ketikan.
     * Cegah "1e309" → INF/NaN crash saat di-cast ke float (field live).
     * Hanya digit & koma (separator desimal), max 1 koma, max 10 karakter.
     */
    public static function inputAttributes(): array
    {
        return [
            'oninput' => "this.value = this.value.replace(/[^0-9,]/g, '').replace(/(,.*),/g, '$1').slice(0, 10)",
        ];
    }

    /**
     * Koma desimal (format Indonesia) → titik untuk validasi numeric & DB.
     */
    public static function normalizeState(mixed $state): mixed
    {
        return is_string($state) ? str_replace(',', '.', $state) : $state;
    }
}
