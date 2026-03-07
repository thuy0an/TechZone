<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\AddToCartRequest;
use App\Services\Interfaces\CartServiceInterface;
use Illuminate\Support\Facades\Auth;

class CartController extends BaseApiController
{
    protected CartServiceInterface $cartService;

    public function __construct(CartServiceInterface $cartService)
    {
        $this->cartService = $cartService;
    }

    // Xem giỏ hàng
    public function index()
    {
        try {
            $userId = Auth::id(); // Lấy ID của user đang đăng nhập
            $cart = $this->cartService->getCart($userId);

            return $this->successResponse($cart, 'Lấy thông tin giỏ hàng thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi hệ thống', $e->getMessage());
        }
    }

    // Thêm sản phẩm vào giỏ
    public function add(AddToCartRequest $request)
    {
        try {
            $userId = Auth::id();

            $cartItem = $this->cartService->addToCart(
                $userId,
                $request->product_id,
                $request->quantity
            );

            return $this->successResponse($cartItem, 'Đã thêm sản phẩm vào giỏ hàng');
        } catch (\Exception $e) {
            return $this->errorResponse('Không thể thêm vào giỏ hàng', 400,  $e->getMessage());
        }
    }

    // Xóa sản phẩm khỏi giỏ
    public function delete($cartItemId)
    {
        try {
            $userId = Auth::id();
            $this->cartService->removeCartItem($userId, $cartItemId);

            return $this->successResponse([], 'Đã xóa sản phẩm khỏi giỏ hàng');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi xóa sản phẩm', 400, $e->getMessage());
        }
    }
}
