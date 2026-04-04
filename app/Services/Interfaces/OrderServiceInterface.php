<?php

namespace App\Services\Interfaces;

interface OrderServiceInterface extends BaseServiceInterface
{
    public function checkout($userId, array $data);
    public function applyPromotion($userId, string $promotionCode, array $selectedProductIds = []): array;
    public function getOrderSummary($userId, $orderId): array;
    public function getMyOrders($userId);
}
