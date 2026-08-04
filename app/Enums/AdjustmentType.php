<?php

namespace App\Enums;

enum AdjustmentType: string
{
    case Increase = 'increase';
    case Decrease = 'decrease';

    public function label(): string
    {
        return match ($this) {
            self::Increase => 'Penambahan',
            self::Decrease => 'Pengurangan',
        };
    }
}
