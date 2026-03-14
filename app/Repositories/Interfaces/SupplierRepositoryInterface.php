<?php

namespace App\Repositories\Interfaces;

interface SupplierRepositoryInterface extends BaseRepositoryInterface
{
    public function getAdminSuppliers(array $filters = [], int $perPage = 15);
    public function hasImportNotes(int $id): bool;
}
