<?php

namespace App\Observers;

use App\Models\Menu;
use Illuminate\Support\Facades\Cache;

class MenuObserver
{
    public function saved(Menu $menu): void
    {
        Cache::forget('customer_menu_v2');
        Cache::forget('menu_categories_cashier');
    }
}
