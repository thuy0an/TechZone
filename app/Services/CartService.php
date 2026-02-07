<?php

namespace App\Services;

use App\Services\Interfaces\CartServiceInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Models\CartItem; // Vẫn cần dùng model con này
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;

class CartService extends BaseService implements CartServiceInterface
{
    protected CartRepositoryInterface $cartRepository;

    public function __construct(CartRepositoryInterface $cartRepository)
    {
        parent::__construct($cartRepository);
        $this->cartRepository = $cartRepository;
    }

    public function getCart($user, $sessionId)
    {
        return $this->cartRepository->findActiveCart($user ? $user->id : null, $sessionId);
    }

    public function addToCart($user, $sessionId, $productId, $quantity)
    {
        // Bọc Transaction thủ công ở đây vì logic phức tạp 
        return DB::transaction(function () use ($user, $sessionId, $productId, $quantity) {
            
            $product = Product::findOrFail($productId);
            
            if ($product->is_hidden) {
                throw new Exception("Sản phẩm này hiện không khả dụng.");
            }

            if ($product->stock_quantity < $quantity) {
                throw new Exception("Sản phẩm chỉ còn {$product->stock_quantity} trong kho.");
            }

            $cart = $this->getCart($user, $sessionId);

            if (!$cart) {
                $cart = $this->repository->create([
                    'user_id' => $user ? $user->id : null,
                    'session_id' => $sessionId,
                    'status' => 'active',
                    'total_price' => 0
                ]);
            }

            $cartItem = $cart->items()->where('product_id', $productId)->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $quantity;
                if ($product->stock_quantity < $newQuantity) {
                    throw new Exception("Tổng số lượng trong giỏ vượt quá tồn kho.");
                }
                $cartItem->quantity = $newQuantity;
                $cartItem->save();
            } else {
                $cart->items()->create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price_at_addition' => $product->current_import_price,
                ]);
            }

            $cart->load('items');

            $this->recalculateTotal($cart);

            return $cart->load('items.product');
        });
    }

    public function updateQuantity($itemId, $quantity)
    {
        $item = CartItem::with('product')->findOrFail($itemId);

        if ($quantity <= 0) {
            return $this->removeItem($itemId);
        }

        if ($item->product->stock_quantity < $quantity) {
            throw new Exception("Kho không đủ hàng (Còn: {$item->product->stock_quantity})");
        }

        $item->quantity = $quantity;
        $item->save();

        $this->recalculateTotal($item->cart);

        return $item->cart->load('items.product');
    }

    public function removeItem($itemId)
    {
        $item = CartItem::findOrFail($itemId);
        $cart = $item->cart;
        $item->delete();

        $this->recalculateTotal($cart);
        return $cart->load('items.product');
    }

    public function clearCart($cartId)
    {
        $cart = $this->repository->findById($cartId);
        $cart->items()->delete();
        $this->repository->update($cartId, ['total_price' => 0]);
        return $cart;
    }

    protected function recalculateTotal($cart)
    {
        $total = $cart->items->sum(function ($item) {
            return $item->quantity * $item->price_at_addition;
        });

        $this->repository->update($cart->id, ['total_price' => $total]);
    }
}