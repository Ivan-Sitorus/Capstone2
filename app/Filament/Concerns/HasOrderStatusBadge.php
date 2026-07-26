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
            'belum_lunas' => 'warning',
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
            'belum_lunas' => 'Belum Lunas',
            default => $state,
        };
    }

    public static function getPaymentLabel(?string $state): string
    {
        return match ($state) {
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            'bayar_nanti' => 'Bayar Nanti',
            'piutang' => 'Piutang',
            default => '-',
        };
    }

    public static function getPaymentStatusColor(?string $state): string
    {
        return match ($state) {
            'lunas' => 'success',
            'belum_lunas' => 'danger',
            default => 'gray',
        };
    }

    public static function getPaymentStatusLabel(?string $state): string
    {
        return match ($state) {
            'lunas' => 'Lunas',
            'belum_lunas' => 'Belum Lunas',
            default => '-',
        };
    }
}
