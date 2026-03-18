<?php

namespace App\Services;

use App\Services\Interfaces\SupplierServiceInterface;
use App\Repositories\Interfaces\SupplierRepositoryInterface;
use App\Repositories\Interfaces\ImportNoteRepositoryInterface;

/**
 * @property SupplierRepositoryInterface $repository
 * @property ImportNoteRepositoryInterface $importNoteRepository
 */
class SupplierService extends BaseService implements SupplierServiceInterface
{
    public function __construct(SupplierRepositoryInterface $repository, ImportNoteRepositoryInterface $importNoteRepository)
    {
        parent::__construct($repository);
        $this->repository = $repository;
        $this->importNoteRepository = $importNoteRepository;
    }

    public function getAdminSuppliersList($request)
    {
        $filters = [
            'search' => $request->input('search'),
        ];
        $perPage = $request->input('per_page', 15);

        return $this->repository->getAdminSuppliers($filters, $perPage);
    }

    public function createSupplier(array $data)
    {
        return $this->repository->create($data);
    }

    public function updateSupplier(int $id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function deleteSupplier(int $id)
    {
        // Logic: Không cho xóa nếu đã nhập hàng từ bên này
        if ($this->repository->hasImportNotes($id)) {
            throw new \Exception('Không thể xóa nhà cung cấp này vì đã có lịch sử nhập kho.');
        }

        return $this->repository->delete($id);
    }

    public function getTransactionHistory(int $id, $request)
    {
        $this->repository->findByIdOrFail($id);

        $filters = $request->only(['start_date', 'end_date']);
        $filters['supplier_id'] = $id;

        $perPage = $request->input('per_page', 10);

        $summary = $this->importNoteRepository->getSupplierTransactionSummary($id, $filters);

        $listFilters = $filters;
        if (isset($listFilters['start_date'])) {
            $listFilters['from_date'] = $listFilters['start_date'];
        }
        if (isset($listFilters['end_date'])) {
            $listFilters['to_date'] = $listFilters['end_date'];
        }

        $transactions = $this->importNoteRepository->getList($listFilters, $perPage);

        return [
            'summary'      => $summary,
            'transactions' => $transactions
        ];
    }
}
