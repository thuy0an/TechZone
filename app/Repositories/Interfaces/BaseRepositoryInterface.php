<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * BaseRepositoryInterface
 * 
 * Interface định nghĩa các phương thức CRUD chuẩn cho Repository pattern
 */
interface BaseRepositoryInterface
{
    /**
     * Lấy tất cả bản ghi
     */
    public function getAll(): Collection;

    /**
     * Lấy bản ghi với phân trang
     */
    public function paginate(int $perPage = 15, array $filters = [], array $sort = []): LengthAwarePaginator;

    /**
     * Tìm bản ghi theo ID
     */
    public function findById(int $id): ?Model;

    /**
     * Tìm bản ghi theo ID hoặc throw exception
     */
    public function findByIdOrFail(int $id): Model;

    /**
     * Tạo bản ghi mới
     */
    public function create(array $data): Model;

    /**
     * Cập nhật bản ghi
     */
    public function update(int $id, array $data): Model;

    /**
     * Xóa bản ghi
     */
    public function delete(int $id): bool;

    /**
     * Xóa mềm bản ghi
     */
    public function softDelete(int $id): bool;

    /**
     * Khôi phục bản ghi đã xóa mềm
     */
    public function restore(int $id): bool;

    /**
     * Tìm kiếm theo điều kiện
     */
    public function findWhere(array $conditions): Collection;

    /**
     * Tìm bản ghi đầu tiên theo điều kiện
     */
    public function findWhereFirst(array $conditions): ?Model;

    /**
     * Đếm số lượng bản ghi
     */
    public function count(): int;

    /**
     * Kiểm tra tồn tại
     */
    public function exists(int $id): bool;
}
