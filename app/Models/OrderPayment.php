<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPayment extends Model
{
    protected $fillable = ['order_id', 'amount', 'payment_date', 'payment_method'];

    protected function casts(): array
    {
        return [
            'payment_date' => 'datetime',
            'amount' => 'integer',
            'payment_method' => PaymentMethod::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
