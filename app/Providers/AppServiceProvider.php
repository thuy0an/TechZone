<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\ProductRepository;
use App\Services\Interfaces\ProductServiceInterface;
use App\Services\ProductService;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\CartRepository;
use App\Services\Interfaces\CartServiceInterface;
use App\Services\CartService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /**
         * Binding Repository
         */
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);

        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);

        /**
         * Binding Service
         */
        $this->app->bind(ProductServiceInterface::class, ProductService::class);

        $this->app->bind(CartServiceInterface::class, CartService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
