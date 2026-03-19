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
        $note = $this->repository->findByIdOrFail($id);

        if ($note->status === 'completed') {
            throw new \Exception('Phiếu nhập này đã được hoàn thành trước đó.');
        }

        DB::beginTransaction();
        try {
            $note->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            // Lấy danh sách chi tiết
            foreach ($note->details as $detail) {
                $product = Product::find($detail->product_id);

                // 1. Lấy Tồn cũ & Giá cũ
                $oldStock = $product->stock_quantity;
                $oldPrice = $product->import_price;

                // 2. Lấy Tồn mới & Giá nhập mới của đợt này
                $newQty   = $detail->quantity;
                $newPrice = $detail->import_price;

                // 3. Tính Tổng tồn kho sau khi nhập
                $totalStock = $oldStock + $newQty;

                // 4. Thuật toán GIÁ BÌNH QUÂN GIA QUYỀN
                $avgPrice = (($oldStock * $oldPrice) + ($newQty * $newPrice)) / $totalStock;

                // 5. Tính Giá bán mới (dựa trên biên lợi nhuận mong muốn)
                $sellingPrice = $avgPrice * (1 + $product->profit_margin);

                // 6. Cập nhật vào Product
                $product->update([
                    'stock_quantity' => $totalStock,
                    'import_price'   => $avgPrice,
                    'selling_price'  => $sellingPrice
                ]);

                // 7. Ghi Lịch sử giá (Yêu cầu quan trọng của Đồ án)
                ProductPriceHistory::create([
                    'product_id'     => $product->id,
                    'import_note_id' => $note->id,
                    'import_price'   => $avgPrice,
                    'profit_margin'  => $product->profit_margin,
                    'selling_price'  => $sellingPrice
                ]);
            }

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
}
