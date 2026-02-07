<?php

namespace App\Repositories\Interfaces;

interface CartRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Tìm giỏ hàng đang active của User hoặc Session
     */
    public function findActiveCart($userId, $sessionId);
}