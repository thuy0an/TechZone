<?php

namespace App\Services\Interfaces;

interface OrderServiceInterface extends BaseServiceInterface
{
    public function checkout($userId, array $data);
    public function getMyOrders($userId);
}
