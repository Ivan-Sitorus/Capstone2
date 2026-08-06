<?php

namespace App\Enums;

enum SourceType: string
{
    case StockAdjustment = 'stock_adjustment';
    case OrderItem = 'order_item';
    case BatchAddition = 'batch_addition';

    public function label(): string
    {
        return match ($this) {
            self::StockAdjustment => 'Penyesuaian Stok',
            self::OrderItem => 'Item Pesanan',
            self::BatchAddition => 'Penambahan Batch',
        };
    }
}
