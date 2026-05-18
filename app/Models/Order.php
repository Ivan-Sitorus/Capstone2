<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const STATUS_PENDING  = 'pending';
    const STATUS_DIPROSES = 'diproses';
    const STATUS_SELESAI  = 'selesai';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($order) {
            // QR orders: code assigned later when customer picks payment method
            if ($order->order_type === 'cashier') {
                $order->order_code ??= static::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $today = today();
        $count = static::whereDate('created_at', $today)
            ->whereNotNull('order_code')
            ->count();
        return 'ORD-' . $today->format('Ymd') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    protected $fillable = [
        'order_code',
        'customer_name',
        'customer_phone',
        'table_id',
        'cashier_id',
        'status',
        'order_type',
        'payment_method',
        'payment_proof',
        'rejection_note',
        'is_paid',
        'total_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'is_paid'      => 'boolean',
        ];
    }

    public function cafeTable()
    {
        return $this->belongsTo(CafeTable::class, 'table_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function appliedPromotions()
    {
        return $this->hasMany(AppliedPromotion::class);
    }

    public function isCashPending(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->payment_method === 'cash';
    }

    public function isQrisPending(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->payment_method === 'qris';
    }

    public function isActive(): bool
    {
        return $this->status !== self::STATUS_SELESAI;
    }
}
