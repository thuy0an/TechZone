<?php

namespace App\Repositories;

use App\Models\Supplier;
use App\Repositories\Interfaces\SupplierRepositoryInterface;

class SupplierRepository extends BaseRepository implements SupplierRepositoryInterface
{
    public function __construct(Supplier $model)
    {
        parent::__construct($model);
    }

    public function getAdminSuppliers(array $filters = [], int $perPage = 15)
    {
        $query = $this->model->newQuery();

        // Tìm kiếm đa trường: Tên, SĐT, Email
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('phone', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function hasImportNotes(int $id): bool
    {
        // Kiểm tra xem nhà cung cấp này có phiếu nhập kho nào không
        return $this->model->where('id', $id)->has('importNotes')->exists();
    }
}
