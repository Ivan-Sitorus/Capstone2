<?php

namespace App\Filament\Support;

class ChartPalette
{
    public static function colors(int $count): array
    {
        $colors = [];

        for ($i = 0; $i < $count; $i++) {
            $hue = (int) round(fmod($i * 137.508, 360));
            $colors[] = sprintf('hsl(%d, 65%%, 55%%)', $hue);
        }

        return $colors;
    }

    /**
     * JS expression for Indonesian (id-ID) number formatting.
     * Thousands separator ".", decimal separator "," (e.g. 1.234,5).
     * Used inside RawJs chart callbacks.
     */
    public static function idNumberFormat(): string
    {
        return "new Intl.NumberFormat('id-ID')";
    }
}
