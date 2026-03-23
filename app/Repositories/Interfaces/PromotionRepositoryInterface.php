<?php

namespace App\Repositories\Interfaces;

interface PromotionRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code);
    public function validatePromotion($promotion, float $cartTotal);
}