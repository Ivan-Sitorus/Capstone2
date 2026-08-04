<?php

namespace App\Enums;

enum AdjustmentCategory: string
{
    case Expired = 'expired';
    case Damaged = 'damaged';
    case Spilled = 'spilled';
    case Complaint = 'complaint';
    case Correction = 'correction';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Expired => 'Kedaluwarsa',
            self::Damaged => 'Rusak',
            self::Spilled => 'Tumpah',
            self::Complaint => 'Keluhan Pelanggan',
            self::Correction => 'Koreksi Stok',
            self::Other => 'Lainnya',
        };
    }
}
