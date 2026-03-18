<?php

namespace App\Repositories\Interfaces;

interface CartRepositoryInterface extends BaseRepositoryInterface
{
    public function getCartByUserId($userId);
    public function updateOrCreateItem($cartId, $productId, $quantity, $currentPrice);
    public function getCartItem($cartId, $productId);
    public function updateCartItemQuantity($item, $quantity, $currentPrice);
    public function deleteCartItem($item);
}
