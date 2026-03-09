<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\CloudinaryService;

use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\ProductRepository;
use App\Services\Interfaces\ProductServiceInterface;
use App\Services\ProductService;

use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\CartRepository;
use App\Services\Interfaces\CartServiceInterface;
use App\Services\CartService;

use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\AuthService;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\OrderRepository;
use App\Services\Interfaces\OrderServiceInterface;
use App\Services\OrderService;

use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\CategoryRepository;
use App\Services\Interfaces\CategoryServiceInterface;
use App\Services\CategoryService;

use App\Repositories\Interfaces\AdminAuthRepositoryInterface;
use App\Repositories\AdminAuthRepository;
use App\Services\Interfaces\AdminAuthServiceInterface;
use App\Services\AdminAuthService;

use App\Repositories\Interfaces\AdminOrderRepositoryInterface;
use App\Repositories\AdminOrderRepository;
use App\Services\Interfaces\AdminOrderServiceInterface;
use App\Services\AdminOrderService;

use App\Repositories\Interfaces\BrandRepositoryInterface;
use App\Repositories\BrandRepository;
use App\Services\Interfaces\BrandServiceInterface;
use App\Services\BrandService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Repositories
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(AdminAuthRepositoryInterface::class, AdminAuthRepository::class);
        $this->app->bind(AdminOrderRepositoryInterface::class, AdminOrderRepository::class);
        $this->app->bind(BrandRepositoryInterface::class, BrandRepository::class);


        // Bind Services
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
        $this->app->bind(CartServiceInterface::class, CartService::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(CategoryServiceInterface::class, CategoryService::class);

        $this->app->bind(AdminAuthServiceInterface::class, AdminAuthService::class);
        $this->app->bind(AdminOrderServiceInterface::class, AdminOrderService::class);
        $this->app->bind(BrandServiceInterface::class, BrandService::class);
        $this->app->singleton(CloudinaryService::class, function ($app) {
            return new CloudinaryService();
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
