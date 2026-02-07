<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\Interfaces\CartServiceInterface;
use App\Http\Requests\Api\AddToCartRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CartController extends BaseApiController
{
    protected $cartService;

    public function __construct(CartServiceInterface $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Helper: Lấy Session ID từ Header (Frontend sẽ gửi lên)
     */
    private function getSessionId(Request $request)
    {
        // Frontend sẽ tự sinh 1 chuỗi UUID và lưu ở LocalStorage, sau đó gửi lên qua Header
        return $request->header('X-Session-ID');
    }

    /**
     * Lấy thông tin giỏ hàng
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::guard('api')->user(); // Hoặc Auth::user() tùy cấu hình
            $sessionId = $this->getSessionId($request);

            $cart = $this->cartService->getCart($user, $sessionId);

            return $this->successResponse($cart, 'Lấy giỏ hàng thành công');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Thêm sản phẩm vào giỏ
     */
    public function addToCart(AddToCartRequest $request)
    {
        try {
            $user = Auth::guard('api')->user();
            $sessionId = $this->getSessionId($request);

            $cart = $this->cartService->addToCart(
                $user,
                $sessionId,
                $request->product_id,
                $request->quantity
            );

            return $this->successResponse($cart, 'Đã thêm vào giỏ hàng', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Cập nhật số lượng
     */
    public function updateQuantity(Request $request, $itemId)
    {
        try {
            $request->validate(['quantity' => 'required|integer|min:0']);
            
            $cart = $this->cartService->updateQuantity($itemId, $request->quantity);

            return $this->successResponse($cart, 'Cập nhật giỏ hàng thành công');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Xóa 1 sản phẩm
     */
    public function removeItem($itemId)
    {
        try {
            $cart = $this->cartService->removeItem($itemId);
            return $this->successResponse($cart, 'Đã xóa sản phẩm khỏi giỏ');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
    
    /**
     * Xóa cả giỏ hàng
     */
    public function clearCart(Request $request)
    {
        try {
             $user = Auth::guard('api')->user();
             $sessionId = $this->getSessionId($request);
             $cart = $this->cartService->getCart($user, $sessionId);
             
             if($cart) {
                 $this->cartService->clearCart($cart->id);
             }

             return $this->successResponse(null, 'Đã làm trống giỏ hàng');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}