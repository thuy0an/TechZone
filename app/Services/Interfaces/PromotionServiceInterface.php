<?php

namespace App\Services\Interfaces;

interface PromotionServiceInterface extends BaseServiceInterface
{
    public function applyPromotion(int $userId, string $code);
}