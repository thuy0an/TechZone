<?php

namespace App\Services\Interfaces;

interface CartServiceInterface extends BaseServiceInterface
{
    public function getCart($userId);
    public function addToCart($userId, $productId, $quantity);
    public function updateCartItem($userId, $productId, $quantity);
    public function removeCartItem($userId, $cartItemId);
}
