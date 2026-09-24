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
}
