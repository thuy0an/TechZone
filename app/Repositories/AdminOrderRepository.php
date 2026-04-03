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

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        // LỌC THEO NGÀY KẾT THÚC
        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['province_id'])) {
            $query->where('province_id', $filters['province_id']);
        }

        if (!empty($filters['district_id'])) {
            $query->where('district_id', $filters['district_id']);
        }

        if (!empty($filters['ward_code'])) {
            $query->where('ward_code', $filters['ward_code']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate($perPage);
    }
}
