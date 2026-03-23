<?php

namespace App\Services;

use App\Services\Interfaces\PromotionServiceInterface;
use App\Repositories\Interfaces\PromotionRepositoryInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use Exception;

class PromotionService extends BaseService implements PromotionServiceInterface
{
    protected CartRepositoryInterface $cartRepository;

    public function __construct(
        PromotionRepositoryInterface $repository,
        CartRepositoryInterface $cartRepository
    ) {
        parent::__construct($repository);
        $this->cartRepository = $cartRepository;
    }

    public function applyPromotion(int $userId, string $code)
    {
        $cart = $this->cartRepository->getCartByUserId($userId);
        if (!$cart || $cart->items->isEmpty()) {
            throw new Exception("Giỏ hàng của bạn đang trống.");
        }

        $cartTotal = $cart->items->sum(function($item) {
            return $item->price_at_addition * $item->quantity;
        });
        $promotion = $this->repository->findByCode($code);
        $validation = $this->repository->validatePromotion($promotion, $cartTotal);

        if (!$validation['is_valid']) {
            throw new Exception($validation['message']);
        }


$discountAmount = 0;
if ($promotion->discount_unit === 'percent') {
    $discountAmount = ($cartTotal * $promotion->discount_value) / 100;
    if ($promotion->max_discount_amount > 0) {
        $discountAmount = min($discountAmount, $promotion->max_discount_amount);
    }
} else {
    $discountAmount = $promotion->discount_value;
}

        $discountAmount = min($discountAmount, $cartTotal);
        $finalTotal = $cartTotal - $discountAmount;

        return [
            'promotion_id'    => $promotion->id,
            'promotion_code'  => $promotion->code,
            'discount_amount' => $discountAmount,
            'original_total'  => $cartTotal,
            'final_total'     => $finalTotal
        ];
    }
}