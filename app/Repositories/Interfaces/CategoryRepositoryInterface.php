<?php

namespace App\Repositories\Interfaces;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Kiểm tra tên category đã tồn tại chưa
     * 
     * @param string $name Tên category cần kiểm tra
     * @param int|null $excludeId ID category cần loại trừ (dùng khi update)
     * @return bool
     */
    public function existsByName(string $name, ?int $excludeId = null): bool;
}