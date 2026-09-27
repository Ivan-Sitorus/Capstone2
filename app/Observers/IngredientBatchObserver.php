<?php

namespace App\Observers;

use App\Models\IngredientBatch;

class IngredientBatchObserver
{
    public function saved(IngredientBatch $batch): void
    {
        // Stok batch berubah -> ketersediaan menu bisa berubah.
        MenuObserver::flushMenuCaches();
    }

    public function deleted(IngredientBatch $batch): void
    {
        MenuObserver::flushMenuCaches();
    }
}
