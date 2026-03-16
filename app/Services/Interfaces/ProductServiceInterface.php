<?php

namespace App\Services\Interfaces;

interface ProductServiceInterface extends BaseServiceInterface
{
    public function getListForStorefront($request);
    public function getDetailForStorefront($id);

    public function getAdminProductsList($request);
    public function createProductForAdmin(array $data);
    public function updateProductForAdmin(int $id, array $data);
    public function deleteProductForAdmin(int $id);
    public function getProductsByCategory(int $categoryId, $request);
    public function getProductPriceHistories(int $productId, $request);
}
