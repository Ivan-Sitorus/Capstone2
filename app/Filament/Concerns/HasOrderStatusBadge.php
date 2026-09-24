<?php

namespace App\Filament\Concerns;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;

trait HasOrderStatusBadge
{
    public static function getStatusColor(string $state): string
    {
        return match (OrderStatus::tryFrom($state)) {
            OrderStatus::Pending => 'warning',
            OrderStatus::Processing => 'info',
            OrderStatus::Completed => 'success',
            OrderStatus::Cancelled => 'danger',
            OrderStatus::Unpaid => 'warning',
            default => 'gray',
        };
    }

    public static function getStatusLabel(string $state): string
    {
        return OrderStatus::tryFrom($state)?->label() ?? $state;
    }

    public static function getPaymentLabel(?string $state): string
    {
        return PaymentMethod::tryFrom((string) $state)?->label() ?? '-';
    }
}
