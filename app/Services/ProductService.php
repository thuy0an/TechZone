<?php

namespace App\Services;

use App\Services\Interfaces\ProductServiceInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\BaseRepositoryInterface;


/**
 * @property ProductRepositoryInterface $repository
 */
class ProductService extends BaseService implements ProductServiceInterface
{
    // protected ProductRepositoryInterface $repository;

    public function __construct(ProductRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function getListForStorefront($request)
    {
        $filters = [
            'search' => $request->get('search'),
            'category_id' => $request->get('category_id'),
        ];

        $perPage = $request->get('per_page', 12);

        return $this->repository->getStorefrontProducts($filters, $perPage);
    }

    public function getDetailForStorefront($id)
    {
        return $this->repository->getVisibleProductById($id);
    }
}
