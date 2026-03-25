<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function getUserOrders($userId, array $filters = [], int $perPage = 10)
    {
        $query = $this->model->with('details.product')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        // Lọc theo mã đơn hàng
        if (!empty($filters['code'])) {
            $query->where('order_code', 'like', '%' . $filters['code'] . '%');
        }
        // Lọc theo trạng thái
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        // Lọc theo ngày bắt đầu
        if (!empty($filters['start_date'])) {
            $query->whereDate('order_date', '>=', $filters['start_date']);
        }
        // Lọc theo ngày kết thúc
        if (!empty($filters['end_date'])) {
            $query->whereDate('order_date', '<=', $filters['end_date']);
        }

        return $query->paginate($perPage);
    }

    public function getUserOrderSummary($userId, $orderId)
    {
        return $this->model->with('details.product')
            ->where('user_id', $userId)
            ->where('id', $orderId)
            ->first();
    }
}
