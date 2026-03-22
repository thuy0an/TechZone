<?php

namespace App\Repositories;

use App\Models\Promotion;
use App\Repositories\Interfaces\PromotionRepositoryInterface;
use Carbon\Carbon;

/**
 * PromotionRepository
 */
class PromotionRepository extends BaseRepository implements PromotionRepositoryInterface
{
    public function __construct(Promotion $model)
    {
        parent::__construct($model);
    }

    public function findActiveByCode(string $code, Carbon $now): ?Promotion
    {
        return $this->model->newQuery()
            ->where('code', $code)
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })
            ->first();
    }

    public function findActiveById(int $id, Carbon $now): ?Promotion
    {
        return $this->model->newQuery()
            ->where('id', $id)
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })
            ->first();
    }

    public function existsByCode(string $code, ?int $excludeId = null): bool
    {
        $query = $this->model->where('code', $code);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Search theo name VÀ code.
     */
    protected function getSearchableFields(): array
    {
        return ['name', 'code'];
    }

    protected function applyFilters($query, array $filters)
    {
        $query = parent::applyFilters($query, $filters);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query;
    }
}
