<?php

namespace App\Services\Interfaces;

/**
 * BaseServiceInterface
 * 
 * Interface cho business logic layer
 */
interface BaseServiceInterface
{
    /**
     * Lấy tất cả bản ghi
     */
    public function getAll();

    /**
     * Lấy bản ghi với phân trang
     */
    public function paginate(int $perPage = 15, array $filters = [], array $sort = []);

    /**
     * Tìm bản ghi theo ID
     */
    public function findById(int $id);

    /**
     * Tạo bản ghi mới
     */
    public function create(array $data);

    /**
     * Cập nhật bản ghi
     */
    public function update(int $id, array $data);

    /**
     * Xóa bản ghi
     */
    public function delete(int $id);
}
