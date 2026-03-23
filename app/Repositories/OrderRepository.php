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
    public function getModel()
{
    return $this->model; 
}

    public function createOrder(array $data) {
        return $this->model->create($data);
    }

    public function createOrderDetail(array $data) {
        return OrderDetail::create($data);
    }

    public function getOrderByCode($code) {
        return $this->model->where('order_code', $code)->with('details.product')->first();
    }

    public function getUserOrders($userId)
    {
        return $this->model->with('details.product')
        ->where('user_id', $userId)
        ->orderBy('created_at', 'desc')
        ->paginate(5);
    }
}
