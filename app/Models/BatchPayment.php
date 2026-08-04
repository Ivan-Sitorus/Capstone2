<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchPayment extends Model
{
    protected $fillable = ['ingredient_batch_id', 'amount', 'payment_date', 'payment_method'];

    protected function casts(): array
    {
        return [
            'payment_date' => 'datetime',
            'amount' => 'integer',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(IngredientBatch::class, 'ingredient_batch_id');
    }
}
