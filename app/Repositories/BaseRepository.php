<?php

namespace App\Repositories;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * BaseRepository
 * 
 * Abstract base repository với các phương thức CRUD chuẩn
 * - CRUD operations
 * - Pagination với filters và sorting
 * - Soft delete support
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    // =========================================
    // READ OPERATIONS
    // =========================================

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function paginate(int $perPage = 15, array $filters = [], array $sort = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Áp dụng filters
        $query = $this->applyFilters($query, $filters);

        // Áp dụng sorting
        $query = $this->applySorting($query, $sort);

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findByIdOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    public function findWhere(array $conditions): Collection
    {
        $query = $this->model->newQuery();

        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->get();
    }

    public function findWhereFirst(array $conditions): ?Model
    {
        $query = $this->model->newQuery();

        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->first();
    }

    public function count(): int
    {
        return $this->model->count();
    }

    public function exists(int $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    // =========================================
    // WRITE OPERATIONS
    // =========================================

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $model = $this->findByIdOrFail($id);
        $model->update($data);
        return $model->fresh();
    }

    public function delete(int $id): bool
    {
        $model = $this->findByIdOrFail($id);
        return $model->delete();
    }

    public function softDelete(int $id): bool
    {
        if (!$this->supportsSoftDeletes()) {
            return false;
        }

        $model = $this->findByIdOrFail($id);
        return $model->delete();
    }

    public function restore(int $id): bool
    {
        if (!$this->supportsSoftDeletes()) {
            return false;
        }

        $model = $this->model->withTrashed()->findOrFail($id);
        return $model->restore();
    }

    // =========================================
    // HELPER METHODS
    // =========================================

    /**
     * Kiểm tra model có hỗ trợ soft delete không
     */
    protected function supportsSoftDeletes(): bool
    {
        return method_exists($this->model, 'restore') &&
            in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(get_class($this->model)));
    }

    /**
     * Áp dụng các điều kiện lọc
     * Override trong subclass để custom logic
     */
    protected function applyFilters($query, array $filters)
    {
        foreach ($filters as $key => $value) {
            if ($value !== null && $value !== '') {
                switch ($key) {
                    case 'search':
                        $query = $this->applySearch($query, $value);
                        break;
                    case 'status':
                        $query->where('status', $value);
                        break;
                    case 'created_from':
                        $query->whereDate('created_at', '>=', $value);
                        break;
                    case 'created_to':
                        $query->whereDate('created_at', '<=', $value);
                        break;
                    default:
                        // Chỉ filter các field trong fillable
                        if (in_array($key, $this->model->getFillable())) {
                            $query->where($key, $value);
                        }
                        break;
                }
            }
        }

        return $query;
    }

    /**
     * Áp dụng tìm kiếm
     * Override trong subclass để custom search fields
     */
    protected function applySearch($query, string $search)
    {
        $searchableFields = $this->getSearchableFields();

        if (!empty($searchableFields)) {
            $query->where(function ($q) use ($search, $searchableFields) {
                foreach ($searchableFields as $field) {
                    $q->orWhere($field, 'LIKE', "%{$search}%");
                }
            });
        }

        return $query;
    }

    /**
     * Lấy các trường có thể search
     * Override trong subclass
     */
    protected function getSearchableFields(): array
    {
        return ['name'];
    }

    /**
     * Áp dụng sắp xếp
     */
    protected function applySorting($query, array $sort)
    {
        $sortBy = $sort['sort_by'] ?? 'created_at';
        $sortOrder = $sort['sort_order'] ?? 'desc';

        $allowedFields = array_merge(
            $this->model->getFillable(),
            ['created_at', 'updated_at', 'id']
        );

        if (in_array($sortBy, $allowedFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query;
    }
}
