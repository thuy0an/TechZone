<?php

namespace App\Services;

use App\Services\Interfaces\AdminOrderServiceInterface;
use App\Repositories\Interfaces\AdminOrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class AdminOrderService extends BaseService implements AdminOrderServiceInterface
{
    protected AdminOrderRepositoryInterface $adminOrderRepository;

    public function __construct(AdminOrderRepositoryInterface $adminOrderRepository)
    {
        parent::__construct($adminOrderRepository);
        $this->adminOrderRepository = $adminOrderRepository;
    }

    public function getListOrders(array $filters, int $perPage = 15)
    {
        return $this->adminOrderRepository->getOrdersWithFilters($filters, $perPage);
    }

    public function updateOrderStatus(int $orderId, string $newStatus)
    {
        $order = $this->adminOrderRepository->findById($orderId);

        if (!$order) {
            throw new Exception("Không tìm thấy đơn hàng.");
        }

        $oldStatus = $order->status;

        // 1. Kiểm tra tính hợp lệ của việc chuyển đổi trạng thái
        $this->validateStatusTransition($oldStatus, $newStatus);

        DB::beginTransaction();
        try {
            // 2. Logic Hoàn lại tồn kho nếu đơn chuyển sang Hủy hoặc Thất bại
            // Chỉ hoàn kho nếu trạng thái cũ KHÔNG PHẢI là cancelled/failed (tránh hoàn cộng dồn nhiều lần)
            if (in_array($newStatus, ['cancelled', 'failed']) && !in_array($oldStatus, ['cancelled', 'failed'])) {
                foreach ($order->details as $detail) {
                    if ($detail->product) {
                        $detail->product->increment('stock_quantity', $detail->quantity);
                    }
                }
            }

            // 3. Logic Trừ lại tồn kho nếu Khôi phục đơn (từ failed/cancelled sang trạng thái khác)
            // (Mở rộng thêm để hệ thống linh hoạt hơn)
            if (in_array($oldStatus, ['cancelled', 'failed']) && !in_array($newStatus, ['cancelled', 'failed'])) {
                foreach ($order->details as $detail) {
                    if ($detail->product) {
                        // Kiểm tra xem kho còn đủ để khôi phục không
                        if ($detail->product->stock_quantity < $detail->quantity) {
                            throw new Exception("Sản phẩm {$detail->product->name} không đủ tồn kho để khôi phục đơn.");
                        }
                        $detail->product->decrement('stock_quantity', $detail->quantity);
                    }
                }
            }

            // 4. Lưu trạng thái mới
            $order->update(['status' => $newStatus]);

            DB::commit();
            return $order;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * State Machine: Ma trận kiểm tra luồng trạng thái hợp lệ
     */
    private function validateStatusTransition(string $current, string $next)
    {
        // Trạng thái giống nhau thì không làm gì cả
        if ($current === $next) return true;

        $validMap = [
            'new'       => ['confirmed', 'cancelled'],
            'confirmed' => ['shipping', 'cancelled'],
            'shipping'  => ['completed', 'failed'],
            'delivered' => ['completed'],
            'completed' => [], // Đã hoàn tất thì đóng băng
            'failed'    => ['shipping', 'cancelled'], // Giao lại hoặc hủy
            // Nếu đơn bị hủy thì hoàn lại tồn kho đã trừ khi tạo hóa đơn
        ];

        if (!in_array($next, $validMap[$current] ?? [])) {
            throw new Exception("Không thể chuyển đơn hàng từ trạng thái [{$current}] sang [{$next}].");
        }

        return true;
    }
}
