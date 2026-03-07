<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\AdminOrderRepositoryInterface;

class AdminOrderRepository extends BaseRepository implements AdminOrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function getOrdersWithFilters(array $filters, int $perPage = 15)
    {
        // Eager load quan hệ với user và chi tiết sản phẩm
        $query = $this->model->with(['user', 'details.product']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('id', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('receiver_phone', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('receiver_name', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
