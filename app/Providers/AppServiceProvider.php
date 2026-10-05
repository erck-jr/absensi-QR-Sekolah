<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiter\Limit;
use Illuminate\Support\Facades\RateLimiter;

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
        // Mendefinisikan Rate Limiter untuk WhatsApp Gateway
        RateLimiter::for('wa-gateway-limiter', function ($job) {
            // Mengizinkan 50 eksekusi setiap 15 menit
            return Limit::perMinutes(15, 50)->by('wa-send');
        });
    }
}
