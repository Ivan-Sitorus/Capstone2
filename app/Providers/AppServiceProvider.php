<?php

namespace App\Providers;

use App\Models\MenuIngredient;
use App\Observers\MenuIngredientObserver;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
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

        Authenticate::redirectUsing(function (Request $request) {
            return $request->is('dapur/*')
                ? route('dapur.login')
                : route('kasir.login');
        });
    }
}
