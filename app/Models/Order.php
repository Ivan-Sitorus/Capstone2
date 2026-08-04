<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_DIPROSES = 'diproses';
    const STATUS_SELESAI = 'selesai';
    const STATUS_DIBATALKAN = 'dibatalkan';

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($order) {
            $order->order_code ??= 'ORD-'.date('dmy').'-'.
                (Order::whereDate('created_at', today())->count() + 1);
        });
    }

    public static function generateCode(): string
    {
        $date = now();
        $orderNumber = static::whereDate('created_at', $date)->count() + 1;
        return 'ORD-'.$date->format('dmy').'-'.str_pad($orderNumber, 4, '0', STR_PAD_LEFT);
    }

    protected $fillable = [
        'order_code',
        'customer_name',
        'phone',
        'table_id',
        'cashier_id',
        'status',
        'order_type',
        'payment_method',
        'payment_proof',
        'rejection_note',
        'total_amount',
        'notes',
        'processed_by',
        'processed_at',
        'completed_at',
        'cancelled_at',
        'uuid',
        'resubmit_count',
        'qris_status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
            'resubmit_count' => 'integer',
            'qris_status' => 'string',
            'processed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function cafeTable(): BelongsTo
    {
        return $this->belongsTo(CafeTable::class, 'table_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function processedBy(): BelongsTo
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
        return $this->status !== self::STATUS_SELESAI && $this->status !== self::STATUS_DIBATALKAN;
    }

    /**
     * Jumlah pesanan pending yang perlu ditangani kasir.
     * Satu sumber kebenaran — dipakai badge sidebar, broadcast, & endpoint count.
     */
    public static function cashierPendingCount(): int
    {
        return static::where('status', self::STATUS_PENDING)
            ->where(fn ($q) => $q->where('order_type', 'cashier')
                ->orWhere(fn ($q2) => $q2->where('order_type', 'qr')
                    ->where(fn ($q3) => $q3->where('payment_method', 'cash')
                        ->orWhere(fn ($q4) => $q4->where('payment_method', 'qris')->whereNotNull('payment_proof'))
                    )
                )
            )->count();
    }

    public function scopeByUuid(Builder $query, string $uuid): Builder
    {
        return $query->where('uuid', $uuid);
    }

    public function isQrisResubmitable(): bool
    {
        return $this->resubmit_count < 3 && $this->qris_status === 'resubmit_requested';
    }

    public function orderPayments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function recalculatePaymentStatus(): void
    {
        $this->load('orderPayments');

        if ($this->payment_method !== 'piutang') {
            return;
        }

        $totalPaid = (float) $this->orderPayments->sum('amount');

        if ($totalPaid > (float) $this->total_amount) {
            throw new \RuntimeException('Total pembayaran melebihi harga pesanan.');
        }

        $this->status = $totalPaid >= (float) $this->total_amount
            ? OrderStatus::Selesai->value
            : OrderStatus::BelumLunas->value;

        $this->saveQuietly();
    }

    public function getReceiptUrlAttribute(): string
    {
        return url('/struk-pesanan/' . $this->uuid);
    }
}
