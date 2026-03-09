<?php

namespace App\Repositories\Interfaces;

interface BrandRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Kiểm tra tên brand đã tồn tại chưa
     *
     * @param string $name Tên brand cần kiểm tra
     * @param int|null $excludeId ID brand cần loại trừ (dùng khi update)
     * @return bool
     */
    public function existsByName(string $name, ?int $excludeId = null): bool;
}
