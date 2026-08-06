<?php

namespace App\Filament\Concerns;

trait HasOrderStatusBadge
{
    public static function getStatusColor(string $state): string
    {
        return match ($state) {
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            'unpaid' => 'warning',
            default => 'gray',
        };
    }

    public static function getStatusLabel(string $state): string
    {
        return match ($state) {
            'pending' => 'Pending',
            'processing' => 'Diproses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'unpaid' => 'Belum Lunas',
            default => $state,
        };
    }

    public static function getPaymentLabel(?string $state): string
    {
        return match ($state) {
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            'pay_later' => 'Bayar Nanti',
            default => '-',
        };
    }

    public static function getPaymentStatusColor(?string $state): string
    {
        return match ($state) {
            'lunas' => 'success',
            'unpaid' => 'danger',
            default => 'gray',
        };
    }

    public static function getPaymentStatusLabel(?string $state): string
    {
        return match ($state) {
            'lunas' => 'Lunas',
            'unpaid' => 'Belum Lunas',
            default => '-',
        };
    }
}
