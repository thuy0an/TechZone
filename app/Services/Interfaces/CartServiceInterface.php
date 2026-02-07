<?php

namespace App\Services\Interfaces;

interface CartServiceInterface extends BaseServiceInterface
{
    public function getCart($user, $sessionId);
    public function addToCart($user, $sessionId, $productId, $quantity);
    public function updateQuantity($itemId, $quantity);
    public function removeItem($itemId);
    public function clearCart($cartId);
}