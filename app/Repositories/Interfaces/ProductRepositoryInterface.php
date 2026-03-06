<?php

namespace App\Repositories\Interfaces;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function getStorefrontProducts($fillters = [], $perPage = 12);
    public function getVisibleProductById($id);
}
