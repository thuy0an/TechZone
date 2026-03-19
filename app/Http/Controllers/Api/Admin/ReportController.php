<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class ReportController extends BaseApiController
{
    // 1. Tra cứu tồn kho tại 1 thời điểm (Historical Stock)
    public function historicalStock(Request $request)
    {
        $request->validate([
            'product_id'  => 'required|integer|exists:products,id',
            'target_date' => 'required|date'
        ]);

        $productId = $request->input('product_id');
        $targetDate = $request->input('target_date');

        try {
            $product = Product::findOrFail($productId);
            // Lưu ý: Đảm bảo bảng products đã có cột initial_quantity, nếu chưa có thì gán mặc định là 0
            $initialQty = $product->initial_quantity ?? 0;

            // Tính Tổng Nhập (Total In)
            $totalIn = DB::table('import_note_details')
                ->join('import_notes', 'import_note_details.import_note_id', '=', 'import_notes.id')
                ->where('import_note_details.product_id', $productId)
                ->where('import_notes.status', 'completed')
                ->where('import_notes.updated_at', '<=', $targetDate)
                ->sum('import_note_details.quantity');

            // Tính Tổng Xuất (Total Out)
            // Lọc bỏ đơn 'cancelled' và 'failed' (thất bại thì hàng hoàn về kho)
            $totalOut = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->where('order_details.product_id', $productId)
                ->whereNotIn('orders.status', ['cancelled', 'failed'])
                ->where('orders.created_at', '<=', $targetDate)
                ->sum('order_details.quantity');

            // Thuật toán: Tồn kho lịch sử = Tồn đầu kỳ + Tổng nhập - Tổng xuất
            $historicalStock = $initialQty + $totalIn - $totalOut;

            return $this->successResponse([
                'product_id'       => $productId,
                'target_date'      => $targetDate,
                'initial_quantity' => $initialQty,
                'total_in'         => (int)$totalIn,
                'total_out'        => (int)$totalOut,
                'historical_stock' => $historicalStock
            ], 'Tra cứu tồn kho lịch sử thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi tra cứu tồn kho', 500, $e->getMessage());
        }
    }

    // 2. Báo cáo tổng nhập - xuất theo khoảng thời gian
    public function importExportReport(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date'
        ]);

        $productId = $request->input('product_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        try {
            // Tổng nhập trong giai đoạn
            $totalIn = DB::table('import_note_details')
                ->join('import_notes', 'import_note_details.import_note_id', '=', 'import_notes.id')
                ->where('import_note_details.product_id', $productId)
                ->where('import_notes.status', 'completed')
                ->whereBetween('import_notes.updated_at', [$startDate, $endDate])
                ->sum('import_note_details.quantity');

            // Tổng xuất trong giai đoạn
            $totalOut = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->where('order_details.product_id', $productId)
                ->whereNotIn('orders.status', ['cancelled', 'failed'])
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->sum('order_details.quantity');

            return $this->successResponse([
                'product_id' => $productId,
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'total_in'   => (int)$totalIn,
                'total_out'  => (int)$totalOut
            ], 'Báo cáo nhập xuất thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi lấy báo cáo', 500, $e->getMessage());
        }
    }
}
