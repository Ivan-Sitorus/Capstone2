<?php

namespace App\Observers;

use App\Models\MenuIngredient;

class MenuIngredientObserver
{
    public function saved(MenuIngredient $menuIngredient): void
    {
        // Resep berubah -> menu spesifik dan cache daftar menu ikut dibersihkan.
        MenuObserver::flushMenuCaches($menuIngredient->menu_id);
    }

    public function deleted(MenuIngredient $menuIngredient): void
    {
        MenuObserver::flushMenuCaches($menuIngredient->menu_id);
    }
}
