<?php

namespace App\Services;

use App\Models\ImportNoteDetail;
use App\Models\ImportNotePayment;
use App\Models\Product;
use App\Models\ProductPriceHistory; // Đảm bảo bạn đã có model này
use App\Repositories\Interfaces\ImportNoteRepositoryInterface;
use App\Services\Interfaces\ImportNoteServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * @property ImportNoteRepositoryInterface $repository
 */
class ImportNoteService extends BaseService implements ImportNoteServiceInterface
{
    public function __construct(ImportNoteRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->repository = $repository;
    }

    public function getImportNotes($request)
    {
        $filters = $request->only(['status', 'supplier_id', 'from_date', 'to_date']);

        return $this->repository->getList($filters, $request->input('per_page', 15));
    }

    public function getImportNoteDetail(int $id)
    {
        return $this->repository->getDetailById($id);
    }

    public function createDraft(int $adminId, array $data)
    {
        DB::beginTransaction();
        try {
            $totalCost = 0;
            foreach ($data['details'] as $item) {
                $totalCost += $item['quantity'] * $item['import_price'];
            }

            // Tạo phiếu nhập
            $note = $this->repository->create([
                'admin_id'    => $adminId,
                'supplier_id' => $data['supplier_id'],
                'import_date' => $data['import_date'],
                'status'      => 'pending',
                'total_cost'  => $totalCost
            ]);

            // Tạo chi tiết
            foreach ($data['details'] as $item) {
                $note->details()->create($item);
            }

            DB::commit();
            return $note;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateDraft(int $id, array $data)
    {
        $note = $this->repository->findByIdOrFail($id);
        if ($note->status !== 'pending') {
            throw new \Exception('Chỉ được sửa phiếu nhập ở trạng thái Pending (Chờ xử lý).');
        }

        DB::beginTransaction();
        try {
            $totalCost = 0;
            foreach ($data['details'] as $item) {
                $totalCost += $item['quantity'] * $item['import_price'];
            }

            $note->update([
                'supplier_id' => $data['supplier_id'],
                'import_date' => $data['import_date'],
                'total_cost'  => $totalCost
            ]);

            // Xóa chi tiết cũ và tạo lại chi tiết mới
            $note->details()->delete();
            foreach ($data['details'] as $item) {
                $note->details()->create($item);
            }

            DB::commit();
            return $note;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // THUẬT TOÁN HOÀN THÀNH PHIẾU NHẬP
    public function completeNote(int $id)
    {
        // Tìm phiếu nhập {id} và Eager load sản phẩm
        $note = $this->repository->model
            ->with(['details.product'])
            ->findOrFail($id);

        // Kiểm tra nếu đã Completed thì return lỗi
        if ($note->status === 'completed') {
            throw new \Exception('Phiếu nhập này đã được hoàn thành trước đó.');
        }

        // Khởi tạo Database Transaction
        DB::beginTransaction();
        try {
            // Lặp qua từng sản phẩm để xử lý
            foreach ($note->details as $detail) {
                $product = $detail->product;

                if (!$product) {
                    throw new \Exception("Không tìm thấy sản phẩm ID: {$detail->product_id}");
                }

                // Lấy dữ liệu hiện tại
                $oldStock = $product->stock_quantity;
                $oldPrice = $product->import_price;
                $profitMargin = $product->profit_margin;

                // Dữ liệu mới từ phiếu nhập
                $newQty   = $detail->quantity;
                $newPrice = $detail->import_price;

                // Áp dụng Thuật toán Giá Bình Quân
                $totalStock = $oldStock + $newQty;
                if ($totalStock == 0) continue; // Bỏ qua nếu lỗi chia cho 0

                $avgPrice = (($oldStock * $oldPrice) + ($newQty * $newPrice)) / $totalStock;

                // Tính Giá Bán mới
                $sellingPrice = $avgPrice * (1 + ($profitMargin / 100));

                // Cập nhật Database (products)
                $updateData = [
                    'stock_quantity' => $totalStock,
                    'import_price'   => $avgPrice,
                    'selling_price'  => $sellingPrice
                ];

                // Nếu sản phẩm đang ở trạng thái hidden (có thể do hết hàng trước đó), tự động chuyển về visible
                if ($product->status === 'hidden') {
                    $updateData['status'] = 'visible';
                }

                $product->update($updateData);

                // Ghi Lịch sử biến động giá
                \App\Models\ProductPriceHistory::create([
                    'product_id'     => $product->id,
                    'import_note_id' => $note->id,
                    'import_price'   => $avgPrice,
                    'profit_margin'  => $profitMargin,
                    'selling_price'  => $sellingPrice
                ]);
            }

            // Chuyển status của phiếu nhập thành Completed
            $note->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            $this->bumpStorefrontCacheVersion();

            // Commit Transaction
            DB::commit();
            return $note;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function recordPayment(int $id, float $amount)
    {
        $note = $this->repository->findById($id);

        if ($note->status !== 'completed') {
            throw new \Exception('Chỉ có thể thanh toán cho phiếu nhập đã duyệt.');
        }

        $remainingDebt = $note->total_cost - $note->paid_amount;

        if ($amount <= 0 || $amount > $remainingDebt) {
            throw new \Exception('Số tiền thanh toán không hợp lệ. Số nợ còn lại là: ' . $remainingDebt);
        }

        return DB::transaction(function () use ($note, $amount) {
            // Lưu vào bảng lịch sử thanh toán
            ImportNotePayment::create([
                'import_note_id' => $note->id,
                'admin_id'       => Auth::id(), // ID của admin đang thao tác
                'amount'         => $amount
            ]);

            // Cập nhật tổng tiền đã trả ở phiếu nhập
            $newPaidAmount = $note->paid_amount + $amount;
            $updatedNote = $this->repository->update($note->id, [
                'paid_amount' => $newPaidAmount
            ]);

            return $updatedNote;
        });
    }

    private function bumpStorefrontCacheVersion(): void
    {
        if (!Cache::has('storefront:products:version')) {
            Cache::put('storefront:products:version', 1);
            return;
        }

        Cache::increment('storefront:products:version');
    }
}
