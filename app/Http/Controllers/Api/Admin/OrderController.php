<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\Order\UpdateStatusRequest;
use App\Services\Interfaces\AdminOrderServiceInterface;
use App\Models\Order;
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
                'payment_method' => $request->query('payment_method'),
                'start_date' => $request->query('start_date'),
                'end_date'   => $request->query('end_date'),
                'province_id'    => $request->query('province_id'),
                'district_id'    => $request->query('district_id'),
                'ward_code'      => $request->query('ward_code'),
                'user_id'        => $request->query('user_id'),
            ];

            $perPage = $request->input('per_page', 15);
            $orders = $this->orderService->getListOrders($filters, $perPage);

            return $this->paginatedResponse($orders, 'Lấy danh sách đơn hàng thành công');
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
            $newStatus = $request->input('status');

            $order = $this->orderService->updateOrderStatus($id, $newStatus);
            return $this->successResponse($order, 'Cập nhật trạng thái thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Không thể cập nhật trạng thái', 400, $e->getMessage(),);
        }
    }
}
