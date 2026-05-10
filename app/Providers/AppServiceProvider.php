<?php

namespace App\Providers;

use App\Models\MenuIngredient;
use App\Observers\MenuIngredientObserver;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\MenuImageService::class);
    }

    public function boot(): void
    {
        MenuIngredient::observe(MenuIngredientObserver::class);

        FilamentAsset::register([
            Css::make('financial-table', __DIR__ . '/../../resources/css/filament/financial-table.css'),
        ]);
    }
}
