<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IngredientBatch extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function scopeAvailable(Builder $query): void
    {
        $query->where('quantity', '>', 0)
            ->where(function (Builder $q) {
                $q->whereNull('expiry_date')
                  ->orWhereDate('expiry_date', '>', now())
                  ->orWhere('allow_expired_usage', true);
            });
    }

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'batch_code',
        'ingredient_id',
        'quantity',
        'expiry_date',
        'received_at',
        'cost_per_unit',
        'custom_order',
        'status',
        'allow_expired_usage',
        'initial_quantity',
        'supplier_name',
        'total_cost',
        'payment_status',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'received_at' => 'datetime',
            'quantity' => 'decimal:2',
            'cost_per_unit' => 'decimal:2',
            'custom_order' => 'integer',
            'status' => 'string',
            'allow_expired_usage' => 'boolean',
            'initial_quantity' => 'decimal:3',
            'total_cost' => 'decimal:2',
            'payment_status' => 'string',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function batchPayments(): HasMany
    {
        return $this->hasMany(BatchPayment::class);
    }

    public function recalculatePaymentStatus(): void
    {
        $this->load('batchPayments');

        $totalPaid = (float) $this->batchPayments->sum('amount');
        $totalCost = (float) ($this->total_cost ?? 0);

        if ($totalPaid > $totalCost) {
            throw new \RuntimeException('Total pembayaran melebihi total harga batch.');
        }

        $this->payment_status = $totalPaid >= $totalCost && $totalCost > 0
            ? 'lunas'
            : 'belum_lunas';

        $this->saveQuietly();
    }
}
