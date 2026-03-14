<?php

namespace App\Services;

use App\Services\Interfaces\SupplierServiceInterface;
use App\Repositories\Interfaces\SupplierRepositoryInterface;

/**
 * @property SupplierRepositoryInterface $repository
 */
class SupplierService extends BaseService implements SupplierServiceInterface
{
    public function __construct(SupplierRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->repository = $repository;
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
}
