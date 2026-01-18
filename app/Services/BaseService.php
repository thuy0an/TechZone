<?php

namespace App\Services;

use App\Services\Interfaces\BaseServiceInterface;
use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BaseService
 * 
 * Abstract service với transaction support cho write operations
 */
abstract class BaseService implements BaseServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(BaseRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    // =========================================
    // READ OPERATIONS
    // =========================================

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function paginate(int $perPage = 15, array $filters = [], array $sort = [])
    {
        return $this->repository->paginate($perPage, $filters, $sort);
    }

    public function findById(int $id)
    {
        return $this->repository->findByIdOrFail($id);
    }

    // =========================================
    // WRITE OPERATIONS (with Transaction)
    // =========================================

    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            // Hook trước khi tạo
            $data = $this->beforeCreate($data);

            $result = $this->repository->create($data);

            // Hook sau khi tạo
            $this->afterCreate($result);

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Create failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            // Hook trước khi cập nhật
            $data = $this->beforeUpdate($id, $data);

            $result = $this->repository->update($id, $data);

            // Hook sau khi cập nhật
            $this->afterUpdate($result);

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Update failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete(int $id)
    {
        DB::beginTransaction();
        try {
            // Hook trước khi xóa
            $this->beforeDelete($id);

            $result = $this->repository->delete($id);

            // Hook sau khi xóa
            $this->afterDelete($id);

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Delete failed: ' . $e->getMessage());
            throw $e;
        }
    }

    // =========================================
    // LIFECYCLE HOOKS (Override trong subclass)
    // =========================================

    /**
     * Hook trước khi tạo
     */
    protected function beforeCreate(array $data): array
    {
        return $data;
    }

    /**
     * Hook sau khi tạo
     */
    protected function afterCreate($model): void
    {
        // Override trong subclass nếu cần
    }

    /**
     * Hook trước khi cập nhật
     */
    protected function beforeUpdate(int $id, array $data): array
    {
        return $data;
    }

    /**
     * Hook sau khi cập nhật
     */
    protected function afterUpdate($model): void
    {
        // Override trong subclass nếu cần
    }

    /**
     * Hook trước khi xóa
     */
    protected function beforeDelete(int $id): void
    {
        // Override trong subclass nếu cần
    }

    /**
     * Hook sau khi xóa
     */
    protected function afterDelete(int $id): void
    {
        // Override trong subclass nếu cần
    }
}
?>
