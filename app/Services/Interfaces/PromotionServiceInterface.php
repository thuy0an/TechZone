<?php

namespace App\Services\Interfaces;

interface PromotionServiceInterface extends BaseServiceInterface
{
    public function createWithProducts(array $data): \App\Models\Promotion;
    public function updateWithProducts(int $id, array $data): \App\Models\Promotion;
    public function toggleActive(int $id): \App\Models\Promotion;
    public function delete(int $id);
    public function paginate(int $perPage = 15, array $filters = [], array $sort = []);
}
