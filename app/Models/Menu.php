<?php

namespace App\Models;

use App\Services\MenuImageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Menu extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'name',
        'price',
        'cashback',
        'image',
        'status',
        'is_student_discount',
        'student_price',
    ];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'cashback' => 'integer',
            'student_price' => 'integer',
            'status' => 'string',
            'is_student_discount' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $menu): void {
            app(MenuImageService::class)->delete($menu->image);
        });
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? url('storage/' . $this->image) : null;
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    public function scopeInactive(Builder $query): void
    {
        $query->where('status', 'inactive');
    }

    /**
     * Calculate how many servings can be made from current stock.
     * NOT an accessor — must be called explicitly when stock display is needed.
     * Use with eager-loaded batches to avoid N+1 queries.
     *
     * @return ?float
     */
    public function computeAvailableServings(): ?float
    {
        $ingredients = $this->menuIngredients()->with('ingredient')->get();
        if ($ingredients->isEmpty()) {
            return null;
        }
        $minServings = null;
        foreach ($ingredients as $mi) {
            if (! $mi->ingredient) continue;
            $totalStock = (float) $mi->ingredient->batches()
                ->where('quantity', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('expiry_date')
                      ->orWhereDate('expiry_date', '>', now())
                      ->orWhere('allow_expired_usage', true);
                })
                ->sum('quantity') ?: 0;
            $needed = (float) $mi->quantity_used;
            $servings = $needed > 0 ? (int) ($totalStock / $needed) : 0;
            if ($minServings === null || $servings < $minServings) {
                $minServings = $servings;
            }
        }
        return $minServings ?? 0;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'menu_ingredients')
            ->withPivot('quantity_used');
    }

    public function menuIngredients(): HasMany
    {
        return $this->hasMany(MenuIngredient::class);
    }

    public function hasRecipe(): bool
    {
        return $this->menuIngredients()->exists();
    }

    public function getEffectivePriceAttribute()
    {
        return $this->student_price ?? $this->price;
    }
}
