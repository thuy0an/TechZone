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

    public function checkout(CheckoutRequest $request)
    {
        try {
            $userId = Auth::id();
            $order = $this->orderService->checkout($userId, $request->validated());

            return $this->createdResponse($order, 'Đặt hàng thành công!');
        } catch (\Exception $e) {
            return $this->errorResponse('Đặt hàng thất bại', 400, $e->getMessage());
        }
    }

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

    
public function summary($id)
{
    try {
    
        $order = $this->orderService->getOrderSummary($id); 

        return response()->json([
            'success' => true,
            'data' => $order
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy thông tin đơn hàng hoặc bạn không có quyền truy cập.'
        ], 404);
    }
}
}
