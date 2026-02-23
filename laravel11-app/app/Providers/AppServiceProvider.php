<?php

namespace App\Providers;

use App\Models\Cuota;
use App\Models\Gastos;
use App\Observers\CuotaObserver;
use App\Observers\GastosObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Cuota::observe(CuotaObserver::class);
        Gastos::observe(GastosObserver::class);
    }
}
