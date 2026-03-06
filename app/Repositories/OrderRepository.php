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

    public function getUserOrders($userId)
    {
        // Lấy danh sách đơn hàng kèm chi tiết sản phẩm, xếp mới nhất lên đầu
        return $this->model->with('details.product')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
