<?php

namespace App\Repositories;

use App\Models\ImportNote;
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
        return $this->model->with(['supplier', 'admin', 'details.product'])->findOrFail($id);
    }
}
