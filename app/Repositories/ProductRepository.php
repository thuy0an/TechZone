<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        return parent::__construct($model);
    }

    public function getStorefrontProducts($filters = [], $perPage = 12)
    {
        $query = $this->model->with(['category', 'brand'])->where('status', 'visible');

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->paginate($perPage);
    }

    public function getVisibleProductById($id)
    {
        return $this->model->with(['category', 'brand'])
            ->where('status', 'visible')
            ->findOrFail($id);
    }
}
