<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CartService; // Додаємо імпорт!

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Реєструємо сервіс як синглтон
        $this->app->singleton(CartService::class, function ($app) {
            return new CartService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}