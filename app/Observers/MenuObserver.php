<?php

namespace App\Observers;

use App\Models\Menu;
use Illuminate\Support\Facades\Cache;

class MenuObserver
{
    public function saved(Menu $menu): void
    {
        Cache::forget('customer_menu');
        Cache::forget('menu_categories_cashier');
        Cache::forget("menu_{$menu->id}");
    }
}
