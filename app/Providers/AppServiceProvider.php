<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <- Agrega esta línea

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
        // Forzar HTTPS en todas las URLs generadas
        if (config('app.env') === 'local') { // Cambia 'local' por 'production' si es servidor real
            URL::forceScheme('http');
        }
    }
}
