<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';

    const STATUS_DIPROSES = 'diproses';

    const STATUS_SELESAI = 'selesai';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($order) {
            $order->order_code ??= 'ORD-'.date('Ymd').'-'.
                str_pad(Order::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        });
    }

    protected static function booted(): void
    {
        static::created(function (self $order) {
            if ($order->payment_method === 'bayar_nanti' && ! $order->is_paid) {
                if (! $order->receivable()->exists()) {
                    Receivable::create([
                        'customer_name' => $order->customer_name ?? 'Event Customer',
                        'amount' => $order->total_amount ?? 0,
                        'invoice_date' => $order->created_at,
                        'due_date' => $order->created_at->copy()->addDays(30),
                        'status' => Receivable::STATUS_PENDING,
                        'paid_amount' => 0,
                        'order_id' => $order->id,
                        'notes' => "Auto-generated from Order #{$order->order_code}",
                    ]);
                }
            }
        });
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
        'processed_by',
        'uuid',
        'resubmit_count',
        'qris_status',
        'whatsapp_phone',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
            'is_paid' => 'boolean',
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

    public function receivable()
    {
        return $this->hasOne(Receivable::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
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
