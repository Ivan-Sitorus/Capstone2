<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Diproses = 'diproses';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Diproses => 'Diproses',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Diproses => 'info',
            self::Selesai => 'success',
            self::Dibatalkan => 'danger',
        };
    }
}
