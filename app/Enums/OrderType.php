<?php

namespace App\Enums;

enum OrderType: string
{
    case Qr = 'qr';
    case Cashier = 'cashier';

    public function label(): string
    {
        return match ($this) {
            self::Qr => 'QR Pelanggan',
            self::Cashier => 'Input Kasir',
        };
    }
}
