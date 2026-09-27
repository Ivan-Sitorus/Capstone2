<?php

namespace App\Observers;

use App\Models\Menu;
use Illuminate\Support\Facades\Cache;

class MenuObserver
{
    /**
     * Invalidate every cache that carries menu availability data.
     *
     * Dipakai bersama oleh observer menu, bahan baku, dan resep agar
     * halaman kasir/pelanggan tidak menyajikan status ketersediaan basi.
     */
    public static function flushMenuCaches(?int $menuId = null): void
    {
        Cache::forget('customer_menu');
        Cache::forget('menu_categories_cashier');
        Cache::forget('menu_categories_cashier_v2');

        if ($menuId !== null) {
            Cache::forget("menu_{$menuId}");
        }
    }

    public function saved(Menu $menu): void
    {
        self::flushMenuCaches($menu->id);
    }
}
