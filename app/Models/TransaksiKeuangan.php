<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiKeuangan extends Model
{
    protected $table = 'transaksi_keuangans';

    public $incrementing = false;

    protected $keyType = 'integer';

    public $timestamps = false;

    protected $fillable = [
        'transaction_type',
        'source',
        'vendor',
        'category',
        'amount',
        'date',
        'description',
        'payment_method',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }
}
