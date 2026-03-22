<?php

namespace App\Repositories;

use App\Models\Promotion;
use App\Repositories\Interfaces\PromotionRepositoryInterface;
use Carbon\Carbon;

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
            ->where(function ($query) use ($now) {
                $query->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })
            ->first();
    }

    public function findActiveById(int $id, Carbon $now): ?Promotion
    {
        return $this->model->newQuery()
            ->where('id', $id)
            ->where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $now);
            })
            ->first();
    }
}
