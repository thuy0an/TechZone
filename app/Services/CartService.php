<?php

namespace App\Services;

use App\Services\Interfaces\CartServiceInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;

/**
 * @property CartRepositoryInterface $repository
 */
class CartService extends BaseService implements CartServiceInterface
{
    protected ProductRepositoryInterface $productRepository;

    public function __construct(CartRepositoryInterface $repository, ProductRepositoryInterface $productRepository)
    {
        parent::__construct($repository);
        $this->productRepository = $productRepository;
    }

    public function getCart($userId)
    {
        // Lấy giỏ hàng và load sẵn các sản phẩm bên trong
        return $this->repository->getCartByUserId($userId);
    }

    public function addToCart($userId, $productId, $quantity)
    {
        $product = $this->productRepository->findById($productId);

        if (!$product || $product->stock_quantity < $quantity) {
            throw new \Exception("Sản phẩm không đủ hàng trong kho.");
        }

        $cart = $this->repository->getCartByUserId($userId);
        return $this->repository->updateOrCreateItem($cart->id, $productId, $quantity, $product->selling_price);
    }

    public function removeCartItem($userId, $cartItemId)
    {
        $cart = $this->repository->getCartByUserId($userId);

        // Chỉ xóa item nếu nó thuộc về giỏ hàng của user này
        $item = \App\Models\CartItem::where('cart_id', $cart->id)
            ->where('id', $cartItemId)
            ->first();

        if (!$item) {
            throw new \Exception("Sản phẩm không tồn tại trong giỏ hàng.");
        }

        return $item->delete();
    }
}
