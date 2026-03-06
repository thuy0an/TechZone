<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\CheckoutRequest;
use App\Services\Interfaces\OrderServiceInterface;
use Illuminate\Support\Facades\Auth;

class OrderController extends BaseApiController
{
    protected OrderServiceInterface $orderService;

    public function __construct(OrderServiceInterface $orderService)
    {
        $this->orderService = $orderService;
    }

    // Chốt đơn hàng
    public function checkout(CheckoutRequest $request)
    {
        try {
            $userId = Auth::id();
            $order = $this->orderService->checkout($userId, $request->validated());

            return $this->successResponse($order, 'Đặt hàng thành công!', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Đặt hàng thất bại', $e->getMessage(), 400);
        }
    }

    // Lịch sử đơn hàng
    public function myOrders()
    {
        try {
            $userId = Auth::id();
            $orders = $this->orderService->getMyOrders($userId);

            return $this->successResponse($orders, 'Lấy danh sách đơn hàng thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi khi lấy danh sách đơn hàng', $e->getMessage());
        }
    }
}
