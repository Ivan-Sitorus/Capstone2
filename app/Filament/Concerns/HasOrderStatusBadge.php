<?php

namespace App\Filament\Concerns;

trait HasOrderStatusBadge
{
    public static function getStatusColor(string $state): string
    {
        return match ($state) {
            'pending' => 'warning',
            'diproses' => 'info',
            'selesai' => 'success',
            'dibatalkan' => 'danger',
            default => 'gray',
        };
    }

    public static function getStatusLabel(string $state): string
    {
        return match ($state) {
            'pending' => 'Pending',
            'diproses' => 'Diproses',
            'selesai' => 'Selesai',
            'dibatalkan' => 'Dibatalkan',
            default => $state,
        };
    }

    public static function getPaymentLabel(?string $state): string
    {
        return match ($state) {
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            'bayar_nanti' => 'Bayar Nanti',
            default => '-',
        };
    }
}
