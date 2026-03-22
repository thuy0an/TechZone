<?php

namespace App\Repositories\Interfaces;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    public function getUserOrders($userId);
    public function getUserOrderSummary($userId, $orderId);
}
