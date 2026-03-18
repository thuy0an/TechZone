<?php

namespace App\Repositories\Interfaces;

interface ImportNoteRepositoryInterface extends BaseRepositoryInterface
{
    public function getList(array $filters, int $perPage);
    public function getDetailById(int $id);
    public function getSupplierTransactionSummary(int $supplierId, array $filters = []);
}
