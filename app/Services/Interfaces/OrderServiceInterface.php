<?php

namespace App\Services\Interfaces;
use Illuminate\Http\Request;
interface OrderServiceInterface extends BaseServiceInterface
{
    public function checkout($userId, array $data);
    public function getMyOrders($userId);
    public function getOrderSummary($orderId);
}
