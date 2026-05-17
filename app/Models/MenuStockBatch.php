<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuStockBatch extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'menu_stock_id',
        'quantity',
        'expiry_date',
        'received_at',
        'cost_per_unit',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'received_at' => 'datetime',
            'quantity' => 'decimal:2',
            'cost_per_unit' => 'decimal:2',
        ];
    }

    public function menuStock()
    {
        return $this->belongsTo(MenuStock::class);
    }
}
