<?php

namespace App\Repositories\Interfaces;

interface AdminOrderRepositoryInterface extends BaseRepositoryInterface
{
    public function getOrdersWithFilters(array $filters, int $perPage = 15);
}
