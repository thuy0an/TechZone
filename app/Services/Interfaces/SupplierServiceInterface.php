<?php

namespace App\Services\Interfaces;

interface SupplierServiceInterface extends BaseServiceInterface
{
    public function getAdminSuppliersList($request);
    public function createSupplier(array $data);
    public function updateSupplier(int $id, array $data);
    public function deleteSupplier(int $id);
    public function getTransactionHistory(int $id, $request);
}
