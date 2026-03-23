<?php

namespace App\Repositories\Interfaces;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function getUserOrders($userId);
    public function createOrder(array $data);
    public function createOrderDetail(array $data);
    public function getOrderByCode($code);
}
