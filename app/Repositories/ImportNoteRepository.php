<?php

namespace App\Repositories;

use App\Models\ImportNote;
use App\Models\ImportNotePayment;
use App\Repositories\Interfaces\ImportNoteRepositoryInterface;

class ImportNoteRepository extends BaseRepository implements ImportNoteRepositoryInterface
{
    public function __construct(ImportNote $model)
    {
        parent::__construct($model);
    }

    public function getList(array $filters, int $perPage)
    {
        $query = $this->model->with(['admin', 'supplier']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('import_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('import_date', '<=', $filters['to_date']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getDetailById(int $id)
    {
        return $this->model->with(['supplier', 'admin', 'details.product', 'payments.admin'])->findOrFail($id);
    }

    public function getSupplierTransactionSummary(int $supplierId, array $filters = [])
    {
        $query = $this->model->where('supplier_id', $supplierId);

        if (!empty($filters['start_date'])) {
            $query->whereDate('import_date', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('import_date', '<=', $filters['end_date']);
        }

        // Tính tổng số phiếu và tổng tiền nhập
        $totalTransactions = $query->count();
        $totalImported = $query->sum('total_cost');

        // Lấy danh sách ID phiếu nhập của Supplier này
        $importNoteIds = $query->pluck('id');

        // Tính tổng tiền đã trả chính xác từ bảng import_note_payments
        $totalPaid = ImportNotePayment::whereIn('import_note_id', $importNoteIds)->sum('amount');

        // TÍNH CÔNG NỢ (Tổng nhập - Tổng trả)
        $totalDebt = $totalImported - $totalPaid;

        return [
            'total_transactions' => $totalTransactions,
            'total_imported'     => (float) $totalImported,
            'total_paid'         => (float) $totalPaid,
            'total_debt'         => (float) $totalDebt,
        ];
    }
}
