<?php

namespace App\Repositories\Interfaces;

interface CartRepositoryInterface extends BaseRepositoryInterface
{
    public function getCartByUserId($userId);
    public function updateOrCreateItem($cartId, $productId, $quantity, $currentPrice);
}
