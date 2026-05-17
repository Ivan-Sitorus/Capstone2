<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class MenuStockMovement extends Model
{
    protected $fillable = [
        'menu_stock_id',
        'menu_stock_batch_id',
        'order_id',
        'order_item_id',
        'menu_stock_adjustment_id',
        'movement_type',
        'source_type',
        'source_id',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'unit_cost',
        'reference',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity_before' => 'decimal:2',
            'quantity_change' => 'decimal:2',
            'quantity_after' => 'decimal:2',
            'unit_cost' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new LogicException('MenuStockMovement records cannot be updated.');
        });

        static::deleting(function () {
            throw new LogicException('MenuStockMovement records cannot be deleted.');
        });
    }

    public function menuStock()
    {
        return $this->belongsTo(MenuStock::class);
    }

    public function batch()
    {
        return $this->belongsTo(MenuStockBatch::class, 'menu_stock_batch_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function adjustment()
    {
        return $this->belongsTo(MenuStockAdjustment::class, 'menu_stock_adjustment_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
