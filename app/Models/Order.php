<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentMethod;
use App\Enums\QrisStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;
use RuntimeException;

class Order extends Model
{
    use HasFactory, HasUuids;

    /**
     * Unique constraints that are safe to recover from by regenerating the
     * order code on the next attempt.
     *
     * @var list<string>
     */
    protected const RETRYABLE_UNIQUE_COLUMNS = ['order_code', 'receipt_token'];

    /** Maximum number of attempts to persist an order with a usable code. */
    protected const MAX_CODE_ATTEMPTS = 5;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($order) {
            $order->order_code ??= static::generateCode();
        });
    }

    /**
     * Create a new order, retrying only when the generated order code (or the
     * receipt token) hits its unique constraint. Any other failure bubbles up
     * untouched, and the retry is capped so a permanently stuck code cannot
     * spin forever.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function create(array $attributes = [])
    {
        return static::retryOnCodeCollision(
            fn () => static::query()->withSavepointIfNeeded(
                fn () => static::query()->create($attributes)
            )
        );
    }

    /**
     * Run the given callback, retrying it only when a unique-constraint
     * violation points at the order code or receipt token. The callback must
     * persist a fresh code on every invocation (the model's creating hook
     * regenerates it for a new instance).
     *
     * @template TReturn
     *
     * @param  callable(int): TReturn  $callback
     * @return TReturn
     */
    public static function retryOnCodeCollision(callable $callback, int $maxAttempts = self::MAX_CODE_ATTEMPTS)
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                return $callback($attempt);
            } catch (UniqueConstraintViolationException $e) {
                if (! static::isRetryableCodeViolation($e)) {
                    throw $e;
                }

                if ($attempt >= $maxAttempts) {
                    throw new RuntimeException(
                        "Gagal membuat kode pesanan unik setelah {$maxAttempts} percobaan.",
                        0,
                        $e,
                    );
                }
            }
        }
    }

    /**
     * Determine whether the unique violation was caused by a retryable
     * order-code / receipt-token constraint rather than another column.
     */
    protected static function isRetryableCodeViolation(UniqueConstraintViolationException $e): bool
    {
        if (! empty($e->columns)) {
            return (bool) array_intersect($e->columns, static::RETRYABLE_UNIQUE_COLUMNS);
        }

        $index = (string) $e->index;

        if ($index === '') {
            return false;
        }

        foreach (static::RETRYABLE_UNIQUE_COLUMNS as $column) {
            if (str_contains($index, $column)) {
                return true;
            }
        }

        return false;
    }

    public function uniqueIds(): array
    {
        return ['receipt_token'];
    }

    public function newUniqueId(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Build the next human-readable order code (e.g. "ORD-270926-0001").
     *
     * The sequence is derived from the highest numeric suffix already stored
     * for today, not from a row count, so gaps left by deletions do not cause
     * a previously used code to be handed out again. Uniqueness under
     * concurrent inserts is guaranteed by the order_code unique index plus the
     * retry loop in create()/retryOnCodeCollision().
     */
    public static function generateCode(): string
    {
        $date = now();
        $prefix = 'ORD-'.$date->format('dmy').'-';
        $offset = strlen($prefix) + 1;

        $row = static::query()
            ->where('order_code', 'like', $prefix.'%')
            ->selectRaw("COALESCE(MAX(CAST(SUBSTR(order_code, {$offset}) AS INTEGER)), 0) AS max_suffix")
            ->first();

        $next = (int) ($row?->max_suffix ?? 0) + 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
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
        'processed_by',
        'processed_at',
        'completed_at',
        'cancelled_at',
        'receipt_token',
        'qris_resubmit_attempts',
        'qris_status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
            'qris_resubmit_attempts' => 'integer',
            'status' => OrderStatus::class,
            'order_type' => OrderType::class,
            'payment_method' => PaymentMethod::class,
            'qris_status' => QrisStatus::class,
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
        return $this->status === OrderStatus::Pending && $this->payment_method === PaymentMethod::Cash;
    }

    public function isQrisPending(): bool
    {
        return $this->status === OrderStatus::Pending && $this->payment_method === PaymentMethod::Qris;
    }

    public function isActive(): bool
    {
        return $this->status !== OrderStatus::Completed && $this->status !== OrderStatus::Cancelled;
    }

    /**
     * Number of pending orders that need cashier attention.
     * Single source of truth: used by sidebar badge and count endpoint.
     */
    public static function cashierPendingCount(): int
    {
        return static::where('status', OrderStatus::Pending)
            ->where(fn ($q) => $q->where('order_type', OrderType::Cashier)
                ->orWhere(fn ($q2) => $q2->where('order_type', OrderType::Qr)
                    ->where(fn ($q3) => $q3->where('payment_method', PaymentMethod::Cash)
                        ->orWhere(fn ($q4) => $q4->where('payment_method', PaymentMethod::Qris)->whereNotNull('payment_proof'))
                    )
                )
            )->count();
    }

    public function scopeByReceiptToken(Builder $query, string $token): Builder
    {
        return $query->where('receipt_token', $token);
    }

    public function isQrisResubmitable(): bool
    {
        return $this->qris_resubmit_attempts < 3 && $this->qris_status === QrisStatus::ResubmitRequested;
    }

    public function orderPayments(): HasMany
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function recalculatePaymentStatus(): void
    {
        $this->load('orderPayments');

        if ($this->payment_method !== PaymentMethod::PayLater) {
            return;
        }

        $totalPaid = (float) $this->orderPayments->sum('amount');

        if ($totalPaid > (float) $this->total_amount) {
            throw new \RuntimeException('Total pembayaran melebihi harga pesanan.');
        }

        $this->status = $totalPaid >= (float) $this->total_amount
            ? OrderStatus::Completed
            : OrderStatus::Unpaid;

        $this->saveQuietly();
    }

    public function getReceiptUrlAttribute(): string
    {
        return url('/struk-pesanan/' . $this->receipt_token);
    }
}
