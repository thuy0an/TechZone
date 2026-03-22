<?php

namespace App\Repositories\Interfaces;

use App\Models\Promotion;
use Carbon\Carbon;

interface PromotionRepositoryInterface extends BaseRepositoryInterface
{
    public function findActiveByCode(string $code, Carbon $now): ?Promotion;
    public function findActiveById(int $id, Carbon $now): ?Promotion;
    public function existsByCode(string $code, ?int $excludeId = null): bool;
}
