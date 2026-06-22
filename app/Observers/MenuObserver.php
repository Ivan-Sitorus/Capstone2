<?php

namespace App\Observers;

use App\Models\Menu;
use App\Models\MenuIngredient;
use App\Models\Ingredient;
use Illuminate\Support\Facades\Cache;

class MenuObserver
{
    /**
     * Auto-create ingredient + recipe for new menus without recipe.
     */
    public function created(Menu $menu): void
    {
        if (! $menu->menuIngredients()->exists()) {
            $ingredient = Ingredient::create([
                'name' => $menu->name,
                'unit' => 'pcs',
                'low_stock_threshold' => 0,
                'batch_mode' => 'fefo',
            ]);

            MenuIngredient::create([
                'menu_id' => $menu->id,
                'ingredient_id' => $ingredient->id,
                'quantity_used' => 1,
            ]);
        }
    }

    public function saved(Menu $menu): void
    {
        Cache::forget('customer_menu_v1');
        Cache::forget('menu_categories_cashier');
    }

    public function deleting(Menu $menu): void {}
    public function restored(Menu $menu): void {}
}
