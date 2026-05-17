<?php

namespace App\Observers;

use App\Models\Menu;
use App\Models\MenuStock;

class MenuObserver
{
    /**
     * Auto-create MenuStock when a non-recipe menu is created.
     */
    public function created(Menu $menu): void
    {
        if (! $menu->is_stock_calculated && ! $menu->menuStock()->exists()) {
            MenuStock::create([
                'menu_id' => $menu->id,
                'unit' => 'pcs',
                'batch_mode' => MenuStock::BATCH_MODE_FEFO,
            ]);
        }
    }

    /**
     * Handle is_stock_calculated toggle (true → false) and serve
     * as an idempotent safety net for MenuStock creation.
     */
    public function saved(Menu $menu): void
    {
        // When ingredients are removed (is_stock_calculated flips to false),
        // auto-create a MenuStock record if one doesn't exist.
        if ($menu->wasChanged('is_stock_calculated')
            && ! $menu->is_stock_calculated
            && ! $menu->menuStock()->exists()) {
            MenuStock::create([
                'menu_id' => $menu->id,
                'unit' => 'pcs',
                'batch_mode' => MenuStock::BATCH_MODE_FEFO,
            ]);
        }
    }

    /**
     * Cascade soft-delete to MenuStock when Menu is soft-deleted.
     */
    public function deleting(Menu $menu): void
    {
        if (! $menu->isForceDeleting()) {
            $menuStock = $menu->menuStock;

            if ($menuStock) {
                $menuStock->delete();
            }
        }
    }

    /**
     * Restore MenuStock when Menu is restored.
     */
    public function restored(Menu $menu): void
    {
        $menuStock = $menu->menuStock()->withTrashed()->first();

        if ($menuStock && $menuStock->trashed()) {
            $menuStock->restore();
        }
    }
}
