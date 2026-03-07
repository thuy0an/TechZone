<?php

namespace App\Services\Interfaces;

interface AdminOrderServiceInterface extends BaseServiceInterface
{
    public function getListOrders(array $filters, int $perPage = 15);
    public function updateOrderStatus(int $orderId, string $newStatus);
}
