<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
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
            // Mengizinkan 35 eksekusi per menit (~525 pesan per 15 menit)
            // Memastikan pengiriman lancar untuk 500-750 siswa pada peak hour
            return Limit::perMinutes(1, 35)->by('wa-send');
        });
    }
}
