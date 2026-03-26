<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\ApplyPromotionRequest;
use App\Http\Requests\CancelOrderRequest;
use App\Services\Interfaces\OrderServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function activePromotions()
    {
        $promotions = \App\Models\Promotion::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->with('products:id')
            ->get(['id', 'name', 'code', 'type', 'discount_value', 'discount_unit', 'min_bill_value', 'max_discount_amount'])
            ->map(function ($p) {
                return [
                    'id'                  => $p->id,
                    'name'                => $p->name,
                    'code'                => $p->code,
                    'type'                => $p->type,
                    'discount_value'      => $p->discount_value,
                    'discount_unit'       => $p->discount_unit,
                    'min_bill_value'      => $p->min_bill_value,
                    'max_discount_amount' => $p->max_discount_amount,
                    'product_ids'         => $p->products->pluck('id')->values(),
                ];
            });

        return $this->successResponse($promotions, 'Lấy danh sách khuyến mãi thành công');
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
    public function myOrders(Request $request)
    {
        try {
            $userId = Auth::id();

            $filters = [
                'code' => $request->get('code'),
                'status' => $request->get('status'),
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date'),
            ];
            $perPage = (int) $request->get('per_page', 10);

            $orders = $this->orderService->getMyOrders($userId, $filters, $perPage);

            return $this->paginatedResponse($orders, 'Lấy danh sách đơn hàng thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi khi lấy danh sách đơn hàng', 400, $e->getMessage());
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

    public function cancelOrder(CancelOrderRequest $request, int $id)
    {
        // Validate input
        $request->validate([
            'cancel_reason' => 'required|string|min:5|max:500',
        ], [
            'cancel_reason.required' => 'Vui lòng chọn lý do hủy đơn.',
            'cancel_reason.min'      => 'Lý do hủy phải có ít nhất 5 ký tự.',
            'cancel_reason.max'      => 'Lý do hủy không được vượt quá 500 ký tự.',
        ]);

        $userId = $request->user()->id;

        // Tìm đơn hàng
        $order = Order::with('details.product')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return $this->notFoundResponse('Không tìm thấy đơn hàng hoặc bạn không có quyền thực hiện thao tác này.');
        }

        // Kiểm tra trạng thái – chỉ cho phép hủy khi đơn đang "new"
        if ($order->status !== 'new') {
            $statusLabels = [
                'confirmed' => 'đã được xác nhận',
                'shipping'  => 'đang được giao',
                'delivered' => 'đã giao đến bạn',
                'completed' => 'đã hoàn thành',
                'cancelled' => 'đã bị hủy trước đó',
                'failed'    => 'đã thất bại',
            ];
            $label = $statusLabels[$order->status] ?? $order->status;
            return $this->errorResponse(
                "Không thể hủy đơn hàng này. Đơn hàng {$label} nên không thể hủy.",
                422
            );
        }

        // Thực hiện hủy trong transaction
        DB::beginTransaction();
        try {
            // Hoàn lại tồn kho
            foreach ($order->details as $detail) {
                if ($detail->product) {
                    $detail->product->increment('stock_quantity', $detail->quantity);
                }
            }

            // Cập nhật trạng thái
            $order->update([
                'status' => 'cancelled',
            ]);

            DB::commit();

            return $this->successResponse([
                'order_id'      => $order->id,
                'order_code'    => $order->order_code,
                'status'        => 'cancelled',
                'cancel_reason' => $request->cancel_reason,
            ], 'Đơn hàng đã được hủy thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CancelOrder failed: ' . $e->getMessage());
            return $this->serverErrorResponse('Không thể hủy đơn hàng. Vui lòng thử lại.');
        }
    }
}
