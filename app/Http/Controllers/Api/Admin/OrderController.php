<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\Order\UpdateStatusRequest;
use App\Services\Interfaces\AdminOrderServiceInterface;
use Illuminate\Http\Request;

class OrderController extends BaseApiController
{
    protected AdminOrderServiceInterface $orderService;

    public function __construct(AdminOrderServiceInterface $orderService)
    {
        $this->orderService = $orderService;
    }

    // Lấy danh sách đơn hàng (Có lọc và phân trang)
    public function index(Request $request)
    {
        try {
            $filters = [
                'status' => $request->input('status'),
                'search' => $request->input('search'),
            ];

            $perPage = $request->input('per_page', 15);
            $orders = $this->orderService->getListOrders($filters, $perPage);

            return $this->successResponse($orders, 'Lấy danh sách đơn hàng thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi lấy danh sách đơn hàng', 500, $e->getMessage());
        }
    }

    // Xem chi tiết 1 đơn hàng
    public function show($id)
    {
        try {
            // Re-use logic findById kèm relation nếu cần
            $order = $this->orderService->findById($id);
            $order->load(['user', 'details.product']);

            return $this->successResponse($order, 'Chi tiết đơn hàng');
        } catch (\Exception $e) {
            return $this->errorResponse('Không tìm thấy đơn hàng', 404, $e->getMessage());
        }
    }

    // Cập nhật trạng thái đơn hàng
    public function updateStatus(UpdateStatusRequest $request, $id)
    {
        try {
            $newStatus = $request->validated()['status'];
            $order = $this->orderService->updateOrderStatus($id, $newStatus);

            return $this->successResponse($order, "Đã cập nhật đơn hàng sang trạng thái: {$newStatus}");
        } catch (\Exception $e) {
            return $this->errorResponse('Không thể cập nhật trạng thái', 400, $e->getMessage(),);
        }
    }
}
