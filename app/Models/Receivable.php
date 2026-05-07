<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receivable extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PARTIAL = 'partial';

    public const STATUS_PAID = 'paid';

    public const STATUS_OVERDUE = 'overdue';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PARTIAL,
        self::STATUS_PAID,
        self::STATUS_OVERDUE,
    ];

    protected $fillable = [
        'customer_name',
        'amount',
        'invoice_date',
        'due_date',
        'status',
        'paid_amount',
        'notes',
        'order_id',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'amount' => 'integer',
            'paid_amount' => 'integer',
        ];
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }

    public function isOverdue(): bool
    {
        return $this->status !== self::STATUS_PAID
            && $this->due_date !== null
            && $this->due_date->lessThan(now());
    }

    /**
     * Record a payment for this receivable.
     * Automatically updates status based on paid_amount.
     *
     * @param float $amount Payment amount to add
     * @throws \InvalidArgumentException If receivable is already paid
     * @return void
     */
    public function recordPayment(float $amount): void
    {
        if ($this->status === self::STATUS_PAID) {
            throw new \InvalidArgumentException('Receivable is already fully paid');
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be positive');
        }

        $currentPaid = (float) $this->paid_amount;
        $totalAmount = (float) $this->amount;
        $newPaid = min($currentPaid + $amount, $totalAmount);

        $this->paid_amount = $newPaid;

        if ($newPaid >= $totalAmount) {
            $this->status = self::STATUS_PAID;
            $this->paid_amount = $totalAmount;
        } elseif ($newPaid > 0) {
            $this->status = self::STATUS_PARTIAL;
        }

        $this->save();
    }

    /**
     * Scope to filter overdue receivables.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', self::STATUS_PAID)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }

    /**
     * Scope to filter pending receivables.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter paid receivables.
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}