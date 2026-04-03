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
        $cart = $this->repository->getCartByUserId($userId);

        if (!$cart || !$cart->relationLoaded('items')) {
            return $cart;
        }

        $cart->items->each(function ($item) {
            $product = $item->product;

            $item->setAttribute('is_price_changed', false);
            $item->setAttribute('old_price', null);
            $item->setAttribute('current_price', null);

            if (!$product) {
                $item->setAttribute('current_price', $this->roundPrice((float) $item->price_at_addition));
                return;
            }

            $savedPrice = $this->roundPrice((float) $item->price_at_addition);
            $newPrice = $this->roundPrice($this->resolveCurrentPrice($product));

            $item->setAttribute('current_price', $newPrice);

            // KIỂM TRA SỰ THAY ĐỔI GIÁ
            if (abs($savedPrice - $newPrice) > 0.00001) {
                $item->setAttribute('is_price_changed', true);
                $item->setAttribute('old_price', $savedPrice);

                // Lưu mức giá mới vào DB ngay lập tức — không block nếu lỗi
                try {
                    $this->repository->updateCartItemPrice($item, $newPrice);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('updateCartItemPrice failed: ' . $e->getMessage());
                }
            }
        });

        return $cart;
    }

    public function addToCart($userId, $productId, $quantity)
    {
        $product = $this->productRepository->findById($productId);

        if (!$product || $product->stock_quantity < $quantity) {
            throw new \Exception("Sản phẩm không đủ hàng trong kho.");
        }

        $cart = $this->repository->getCartByUserId($userId);
        $currentPrice = $this->resolveCurrentPrice($product);
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
            $this->repository->updateCartItemQuantity($existingItem, $nextQuantity);
            return $this->repository->getCartByUserId($userId);
        }

        $currentPrice = $this->resolveCurrentPrice($product);
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

    private function roundPrice(float $price): float
    {
        return round($price, 2);
    }

    private function resolveCurrentPrice($product): float
    {
        if (isset($product->selling_price) && (float) $product->selling_price > 0) {
            return (float) $product->selling_price;
        }

        return (float) $product->import_price * (1 + ((float) $product->profit_margin / 100));
    }
}
