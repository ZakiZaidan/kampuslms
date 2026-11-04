<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
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
        // BREAK 1 (w11): Aktifkan baris di bawah ini untuk mendeteksi seluruh N+1 query:
        // Model::preventLazyLoading(! app()->isProduction());
    }
}
