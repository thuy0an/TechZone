<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\ApplyPromotionRequest;
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
    public function createOrder(CreateOrderRequest $request)
    {
        try {
            $userId = Auth::id();
            $result = $this->orderService->checkout($userId, $request->validated());
            $payload = $result['payload'];

            if ($payload['payment_method'] === 'bank_transfer') {
                $bankInfo = config('payment.bank_transfer');
                $payload['bank_transfer_info'] = [
                    'bank_name' => $bankInfo['bank_name'],
                    'account_number' => $bankInfo['account_number'],
                    'account_owner' => $bankInfo['account_owner'],
                    'transfer_note' => 'Thanh toan don hang ' . $payload['order_id'],
                ];
            }

            if ($payload['payment_method'] === 'online') {
                $baseUrl = config('payment.online.mock_payment_base_url');
                $payload['mock_payment_url'] = $baseUrl . '?order_id=' . $payload['order_id'];
            }

            return $this->createdResponse($payload, 'Đặt hàng thành công!');
        } catch (\Exception $e) {
            return $this->errorResponse('Đặt hàng thất bại', 400, $e->getMessage());
        }
    }

    public function applyPromotion(ApplyPromotionRequest $request)
    {
        try {
            $userId = Auth::id();
            $result = $this->orderService->applyPromotion($userId, $request->promotion_code);

            return $this->successResponse($result, 'Áp dụng khuyến mãi thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Áp dụng khuyến mãi thất bại', 400, $e->getMessage());
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

    public function orderSummary($id)
    {
        try {
            $userId = Auth::id();
            $summary = $this->orderService->getOrderSummary($userId, $id);

            return $this->successResponse($summary, 'Lấy tóm tắt đơn hàng thành công');
        } catch (\Exception $e) {
            if ($e->getMessage() === 'forbidden') {
                return $this->forbiddenResponse('Bạn không có quyền xem đơn hàng này.');
            }
            return $this->errorResponse('Lỗi khi lấy tóm tắt đơn hàng', 400, $e->getMessage());
        }
    }
}
