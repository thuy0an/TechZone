<?php

namespace App\Repositories\Interfaces;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function getStorefrontProducts($fillters = [], $perPage = 12);
    public function getVisibleProductById($id);

    public function getAdminProducts(array $filters = [], int $perPage = 15);
    public function softDeleteProduct(int $id);
    public function hasActiveOrders(int $productId);
    public function forceDelete(int $id);
}
