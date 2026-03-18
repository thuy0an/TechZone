<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartRepositoryInterface;

class CartRepository extends BaseRepository implements CartRepositoryInterface
{
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }

    public function getCartByUserId($userId)
    {
        return $this->model->with('items.product')->firstOrCreate(['user_id' => $userId]);
    }

    public function updateOrCreateItem($cartId, $productId, $quantity, $currentPrice)
    {
        $item = CartItem::where('cart_id', $cartId)->where('product_id', $productId)->first();

        if ($item) {
            $item->increment('quantity', $quantity);
            $item->update(['price_at_addition' => $currentPrice]);
            return $item;
        }

        return CartItem::create([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'price_at_addition' => $currentPrice
        ]);
    }

    public function getCartItem($cartId, $productId)
    {
        return CartItem::where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();
    }

    public function updateCartItemQuantity($item, $quantity, $currentPrice)
    {
        $item->update([
            'quantity' => $quantity,
            'price_at_addition' => $currentPrice,
        ]);

        return $item;
    }

    public function deleteCartItem($item)
    {
        return $item->delete();
    }
}
