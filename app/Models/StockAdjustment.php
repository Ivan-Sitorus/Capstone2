<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    public const TYPE_INCREASE = 'increase';

    public const TYPE_DECREASE = 'decrease';

    public const TYPES = [
        self::TYPE_INCREASE,
        self::TYPE_DECREASE,
    ];

    protected $fillable = [
        'ingredient_id',
        'adjustment_type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reason',
        'reported_by',
        'adjusted_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'quantity_before' => 'decimal:2',
            'quantity_after' => 'decimal:2',
            'adjusted_at' => 'datetime',
        ];
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
