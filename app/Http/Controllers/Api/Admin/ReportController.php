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


    // ─────────────────────────────────────────────────────────────
    // 1. Báo cáo Doanh thu & Lợi nhuận gộp
    //    GET /api/admin/reports/revenue-profit
    //    ?period=daily|monthly  &start_date=YYYY-MM-DD  &end_date=YYYY-MM-DD
    // ─────────────────────────────────────────────────────────────
    public function revenueProfit(Request $request)
    {
        $request->validate([
            'period'     => 'required|in:daily,monthly',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $period    = $request->input('period');
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date') . ' 23:59:59';

        // Sử dụng biến string trực tiếp để tránh lỗi binding trong định dạng DATE_FORMAT của một số phiên bản DB
        $groupFormat = $period === 'daily' ? '%Y-%m-%d' : '%Y-%m';

        try {
            $rows = DB::table('orders')
                ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                ->join('products', 'order_details.product_id', '=', 'products.id')
                ->where('orders.status', 'completed')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->selectRaw("
                    DATE_FORMAT(orders.created_at, '{$groupFormat}') AS period_label,
                    SUM(order_details.quantity * order_details.unit_price) AS revenue,
                    SUM(order_details.quantity * COALESCE(products.import_price, 0)) AS cost
                ")
                ->groupBy(DB::raw("DATE_FORMAT(orders.created_at, '{$groupFormat}')"))
                ->orderBy(DB::raw("DATE_FORMAT(orders.created_at, '{$groupFormat}')"), 'asc')
                ->get();

            $data = $rows->map(fn($r) => [
                'label'        => $r->period_label,
                'revenue'      => (float) $r->revenue,
                'cost'         => (float) $r->cost,
                'gross_profit' => (float) $r->revenue - (float) $r->cost,
            ]);

            $totalRevenue = $data->sum('revenue');
            $totalCost    = $data->sum('cost');

            return $this->successResponse([
                'period'             => $period,
                'start_date'         => $startDate,
                'end_date'           => $endDate,
                'chart_data'         => $data->values(),
                'total_revenue'      => $totalRevenue,
                'total_cost'         => $totalCost,
                'total_gross_profit' => $totalRevenue - $totalCost,
            ], 'Báo cáo doanh thu & lợi nhuận thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi báo cáo doanh thu', 500, $e->getMessage());
        }
    }
    // ─────────────────────────────────────────────────────────────
    // 2. Báo cáo Dòng tiền (Cash Flow)
    //    GET /api/admin/reports/cash-flow
    //    ?period=daily|monthly  &start_date=YYYY-MM-DD  &end_date=YYYY-MM-DD
    // ─────────────────────────────────────────────────────────────
    public function cashFlow(Request $request)
    {
        $request->validate([
            'period'     => 'required|in:daily,monthly',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $period      = $request->input('period');
        $startDate   = $request->input('start_date');
        $endDate     = $request->input('end_date') . ' 23:59:59';
        $groupFormat = $period === 'daily' ? '%Y-%m-%d' : '%Y-%m';

        try {
            // Tiền thu vào: đơn hàng completed
            $inflow = DB::table('orders')
                ->where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                // Sửa lỗi: Đưa format trực tiếp vào chuỗi và đồng bộ select/group
                ->selectRaw("DATE_FORMAT(created_at, '{$groupFormat}') AS period_label, SUM(total_amount) AS amount")
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '{$groupFormat}')"))
                ->pluck('amount', 'period_label');

            // Tiền chi ra: thanh toán phiếu nhập
            $outflow = DB::table('import_note_payments')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw("DATE_FORMAT(created_at, '{$groupFormat}') AS period_label, SUM(amount) AS amount")
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '{$groupFormat}')"))
                ->pluck('amount', 'period_label');

            // Merge tất cả labels và sắp xếp
            $labels = collect($inflow->keys())->merge($outflow->keys())->unique()->sort()->values();

            $chartData = $labels->map(fn($label) => [
                'label'   => $label,
                'inflow'  => (float)($inflow[$label]  ?? 0),
                'outflow' => (float)($outflow[$label] ?? 0),
                'net'     => (float)($inflow[$label] ?? 0) - (float)($outflow[$label] ?? 0),
            ]);

            return $this->successResponse([
                'period'        => $period,
                'chart_data'    => $chartData->values(),
                'total_inflow'  => (float)$inflow->sum(),
                'total_outflow' => (float)$outflow->sum(),
                'net_cash_flow' => (float)($inflow->sum() - $outflow->sum()),
            ], 'Báo cáo dòng tiền thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi báo cáo dòng tiền', 500, $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 3. Top sản phẩm bán chạy
    //    GET /api/admin/reports/best-sellers
    //    ?sort_by=quantity|revenue  &limit=10  &start_date=  &end_date=
    // ─────────────────────────────────────────────────────────────
    public function bestSellers(Request $request)
    {
        $request->validate([
            'sort_by'    => 'required|in:quantity,revenue',
            'limit'      => 'nullable|integer|min:5|max:50',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
        ]);

        $sortBy    = $request->input('sort_by', 'quantity');
        $limit     = (int) $request->input('limit', 10);
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date') ? $request->input('end_date') . ' 23:59:59' : null;

        try {
            $query = DB::table('order_details')
                ->join('orders',   'order_details.order_id',   '=', 'orders.id')
                ->join('products', 'order_details.product_id', '=', 'products.id')
                ->where('orders.status', 'completed')
                ->select(
                    'products.id',
                    'products.code',
                    'products.name',
                    DB::raw("SUM(order_details.quantity) AS total_qty"),
                    DB::raw("SUM(order_details.quantity * order_details.unit_price) AS total_revenue")
                )
                // Sửa lỗi: Đảm bảo groupBy chứa đầy đủ các cột non-aggregated trong SELECT
                ->groupBy('products.id', 'products.code', 'products.name');

            if ($startDate) {
                $query->where('orders.created_at', '>=', $startDate);
            }
            if ($endDate) {
                $query->where('orders.created_at', '<=', $endDate);
            }

            $orderCol = $sortBy === 'quantity' ? 'total_qty' : 'total_revenue';
            $items    = $query->orderByDesc($orderCol)->limit($limit)->get();

            return $this->successResponse([
                'sort_by' => $sortBy,
                'items'   => $items->map(fn($r) => [
                    'id'            => $r->id,
                    'code'          => $r->code,
                    'name'          => $r->name,
                    'total_qty'     => (int) $r->total_qty,
                    'total_revenue' => (float) $r->total_revenue,
                ]),
            ], 'Top sản phẩm bán chạy thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi top sản phẩm', 500, $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 4. Hàng chậm luân chuyển (Dead / Slow-moving Stock)
    //    GET /api/admin/reports/slow-moving-stock
    //    ?days=30|60|90
    // ─────────────────────────────────────────────────────────────
    public function slowMovingStock(Request $request)
    {
        $request->validate([
            'days' => 'required|in:30,60,90',
        ]);

        $days   = (int) $request->input('days');
        $cutoff = now()->subDays($days)->toDateTimeString();

        try {
            // Lấy product_id có bán trong `days` ngày gần nhất
            $soldIds = DB::table('order_details')
                ->join('orders', 'order_details.order_id', '=', 'orders.id')
                ->where('orders.status', 'completed')
                ->where('orders.created_at', '>=', $cutoff)
                ->distinct()
                ->pluck('order_details.product_id');

            $items = DB::table('products')
                ->whereNotIn('id', $soldIds)
                ->where('stock_quantity', '>', 0)
                ->where('status', 'active')
                ->select(
                    'id',
                    'code',
                    'name',
                    'stock_quantity',
                    'import_price',
                    DB::raw('stock_quantity * COALESCE(import_price, 0) AS stock_value')
                )
                ->orderByDesc('stock_value')
                ->get();

            return $this->successResponse([
                'days'           => $days,
                'cutoff_date'    => $cutoff,
                'total_products' => $items->count(),
                'total_value'    => $items->sum('stock_value'),
                'items'          => $items->map(fn($r) => [
                    'id'             => $r->id,
                    'code'           => $r->code,
                    'name'           => $r->name,
                    'stock_quantity' => (int) $r->stock_quantity,
                    'import_price'   => (float) $r->import_price,
                    'stock_value'    => (float) $r->stock_value,
                ]),
            ], 'Báo cáo hàng chậm luân chuyển thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi báo cáo hàng chậm', 500, $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 5. Tỷ lệ trạng thái đơn hàng (Order Status Analytics)
    //    GET /api/admin/reports/order-status
    //    ?start_date=  &end_date=
    // ─────────────────────────────────────────────────────────────
    public function orderStatusAnalytics(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
        ]);

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date')
            ? $request->input('end_date') . ' 23:59:59'
            : null;

        try {
            $query = DB::table('orders')
                ->select('status', DB::raw('COUNT(*) AS total'))
                ->groupBy('status');

            if ($startDate) $query->where('created_at', '>=', $startDate);
            if ($endDate)   $query->where('created_at', '<=', $endDate);

            $rows       = $query->get();
            $grandTotal = $rows->sum('total');

            $items = $rows->map(fn($r) => [
                'status'     => $r->status,
                'total'      => (int) $r->total,
                'percentage' => $grandTotal > 0
                    ? round((int) $r->total / $grandTotal * 100, 1)
                    : 0,
            ])->sortByDesc('total')->values();

            return $this->successResponse([
                'grand_total' => $grandTotal,
                'items'       => $items,
            ], 'Phân tích trạng thái đơn hàng thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi phân tích đơn hàng', 500, $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 6. Phân bổ doanh thu theo khu vực (Sales by Region)
    //    GET /api/admin/reports/sales-by-region
    //    ?start_date=  &end_date=  &limit=15
    // ─────────────────────────────────────────────────────────────
    public function salesByRegion(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date',
            'limit'      => 'nullable|integer|min:5|max:63',
        ]);

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date')
            ? $request->input('end_date') . ' 23:59:59'
            : null;
        $limit = (int) $request->input('limit', 15);

        try {
            $query = DB::table('orders')
                ->where('status', 'completed')
                ->whereNotNull('province_name')
                ->select(
                    'province_name',
                    DB::raw('COUNT(*) AS order_count'),
                    DB::raw('SUM(total_amount) AS revenue')
                )
                ->groupBy('province_name');

            if ($startDate) $query->where('created_at', '>=', $startDate);
            if ($endDate)   $query->where('created_at', '<=', $endDate);

            // Chuyển order và limit xuống cuối cùng trước khi get()
            $rows         = $query->orderByDesc('revenue')->limit($limit)->get();
            $totalRevenue = $rows->sum('revenue');

            $items = $rows->map(fn($r) => [
                'province_name' => $r->province_name,
                'order_count'   => (int) $r->order_count,
                'revenue'       => (float) $r->revenue,
                'percentage'    => $totalRevenue > 0
                    ? round((float) $r->revenue / $totalRevenue * 100, 1)
                    : 0,
            ]);

            return $this->successResponse([
                'total_revenue' => $totalRevenue,
                'items'         => $items,
            ], 'Phân bổ doanh thu theo khu vực thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi phân bổ khu vực', 500, $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // 7. Tổng hợp công nợ nhà cung cấp (Supplier Payable)
    //    GET /api/admin/reports/supplier-payable
    // ─────────────────────────────────────────────────────────────
    public function supplierPayable(Request $request)
    {
        try {
            $rows = DB::table('import_notes')
                ->join('suppliers', 'import_notes.supplier_id', '=', 'suppliers.id')
                ->whereColumn('import_notes.total_cost', '>', 'import_notes.paid_amount')
                ->whereIn('import_notes.status', ['completed', 'partial'])
                ->select(
                    'suppliers.id AS supplier_id',
                    'suppliers.name AS supplier_name',
                    'suppliers.phone AS supplier_phone',
                    DB::raw('COUNT(import_notes.id) AS note_count'),
                    DB::raw('SUM(import_notes.total_cost) AS total_cost'),
                    DB::raw('SUM(import_notes.paid_amount) AS total_paid'),
                    DB::raw('SUM(import_notes.total_cost - import_notes.paid_amount) AS remaining_debt')
                )
                ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.phone')
                ->orderByDesc('remaining_debt')
                ->get();

            return $this->successResponse([
                'total_suppliers' => $rows->count(),
                'grand_debt'      => $rows->sum('remaining_debt'),
                'items'           => $rows->map(fn($r) => [
                    'supplier_id'    => $r->supplier_id,
                    'supplier_name'  => $r->supplier_name,
                    'supplier_phone' => $r->supplier_phone,
                    'note_count'     => (int) $r->note_count,
                    'total_cost'     => (float) $r->total_cost,
                    'total_paid'     => (float) $r->total_paid,
                    'remaining_debt' => (float) $r->remaining_debt,
                ]),
            ], 'Tổng hợp công nợ nhà cung cấp thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi tổng hợp công nợ', 500, $e->getMessage());
        }
    }
}
