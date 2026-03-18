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
        $currentPrice = (float) $product->import_price * (1 + ((float) $product->profit_margin / 100));
        return $this->repository->updateOrCreateItem($cart->id, $productId, $quantity, $currentPrice);
    }

    public function updateCartItem($userId, $productId, $quantity)
    {
        $product = $this->productRepository->findById($productId);

        if (!$product) {
            throw new \Exception('Sản phẩm không tồn tại trong hệ thống.');
        }

        $cart = $this->repository->getCartByUserId($userId);
        $existingItem = $this->repository->getCartItem($cart->id, $productId);

        $currentPrice = (float) $product->import_price * (1 + ((float) $product->profit_margin / 100));
        $currentQuantity = $existingItem ? (int) $existingItem->quantity : 0;
        $delta = (int) $quantity;

        if ($delta === 0) {
            if ($existingItem) {
                $this->repository->deleteCartItem($existingItem);
            }
            return $this->repository->getCartByUserId($userId);
        }

        $nextQuantity = $currentQuantity + $delta;

        if ($nextQuantity > (int) $product->stock_quantity) {
            throw new \Exception('Vượt quá số lượng tồn kho');
        }

        if (!$existingItem && $nextQuantity <= 0) {
            return $this->repository->getCartByUserId($userId);
        }

        if ($existingItem && $nextQuantity <= 0) {
            $this->repository->deleteCartItem($existingItem);
            return $this->repository->getCartByUserId($userId);
        }

        if ($existingItem) {
            $this->repository->updateCartItemQuantity($existingItem, $nextQuantity, $currentPrice);
            return $this->repository->getCartByUserId($userId);
        }

        $this->repository->updateOrCreateItem($cart->id, $productId, $nextQuantity, $currentPrice);
        return $this->repository->getCartByUserId($userId);
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
