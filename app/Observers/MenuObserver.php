<?php

namespace App\Observers;

use App\Models\Menu;
use Illuminate\Support\Facades\Cache;

class MenuObserver
{
    public function saved(Menu $menu): void
    {
        $this->forgetCachedMenu($menu);
    }

    public function deleted(Menu $menu): void
    {
        // Soft deletes also fire `deleted`; without this the cached model would
        // keep an orderable menu alive after it was removed from the catalog.
        $this->forgetCachedMenu($menu);
    }

    private function forgetCachedMenu(Menu $menu): void
    {
        Cache::forget('customer_menu');
        Cache::forget('menu_categories_cashier');
        Cache::forget('menu_categories_cashier_v2');
        Cache::forget("menu_{$menu->id}");
    }
}
