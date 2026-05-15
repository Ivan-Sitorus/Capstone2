<?php

namespace App\Models;

use App\Services\MenuImageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Menu extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'price',
        'cashback',
        'image',
        'is_available',
        'is_stock_calculated',
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
            'is_available' => 'boolean',
            'is_stock_calculated' => 'boolean',
            'is_student_discount' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $menu): void {
            if (blank($menu->slug)) {
                $menu->slug = Str::slug($menu->name);
            }
        });

        static::deleting(function (self $menu): void {
            app(MenuImageService::class)->delete($menu->image);
        });
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? app(MenuImageService::class)->getImageUrl($this->image) : null;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'menu_ingredients')
            ->withPivot('quantity_used');
    }

    public function menuIngredients()
    {
        return $this->hasMany(MenuIngredient::class);
    }

    public function calculateCost(): float
    {
        $this->loadMissing('menuIngredients.ingredient');

        $totalCost = 0.0;

        foreach ($this->menuIngredients as $menuIngredient) {
            $ingredient = $menuIngredient->ingredient;

            if (! $ingredient) {
                continue;
            }

            $averageCost = (float) ($ingredient->batches()
                ->where('quantity', '>', 0)
                ->avg('cost_per_unit') ?? 0);

            $totalCost += $averageCost * (float) $menuIngredient->quantity_used;
        }

        return round($totalCost, 2);
    }

    public function hasRecipe(): bool
    {
        return $this->menuIngredients()->exists();
    }

    public function refreshStockCalculatedFlag(): void
    {
        $hasRecipe = $this->menuIngredients()->exists();

        if ($this->is_stock_calculated !== $hasRecipe) {
            $this->forceFill([
                'is_stock_calculated' => $hasRecipe,
            ])->saveQuietly();
        }
    }

    public function getEffectivePriceAttribute(): string
    {
        return ($this->is_student_discount && $this->student_price)
            ? $this->student_price
            : $this->price;
    }
}
