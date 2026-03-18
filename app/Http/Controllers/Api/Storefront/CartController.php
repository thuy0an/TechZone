<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Services\Interfaces\CartServiceInterface;
use Illuminate\Http\Request;

class CartController extends BaseApiController
{
    protected CartServiceInterface $cartService;

    public function __construct(CartServiceInterface $cartService)
    {
        $this->cartService = $cartService;
    }

    // Xem giỏ hàng
    public function index(Request $request)
    {
        try {
            $userId = $request->user('sanctum')->id; // Lấy ID của user đang đăng nhập
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
            $userId = $request->user('sanctum')->id;

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

    // Cập nhật số lượng sản phẩm trong giỏ
    public function update(UpdateCartRequest $request)
    {
        try {
            $userId = $request->user('sanctum')->id;

            $cart = $this->cartService->updateCartItem(
                $userId,
                $request->product_id,
                $request->quantity
            );

            return $this->successResponse($cart, 'Cập nhật giỏ hàng thành công');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    // Xóa sản phẩm khỏi giỏ
    public function delete(Request $request, $cartItemId)
    {
        try {
            $userId = $request->user('sanctum')->id;
            $this->cartService->removeCartItem($userId, $cartItemId);

            return $this->successResponse([], 'Đã xóa sản phẩm khỏi giỏ hàng');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi xóa sản phẩm', 400, $e->getMessage());
        }
    }
}
