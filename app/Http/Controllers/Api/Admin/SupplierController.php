<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\Supplier\StoreSupplierRequest;
use App\Http\Requests\Admin\Supplier\UpdateSupplierRequest;
use App\Services\Interfaces\SupplierServiceInterface;
use Illuminate\Http\Request;

class SupplierController extends BaseApiController
{
    protected SupplierServiceInterface $supplierService;

    public function __construct(SupplierServiceInterface $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    public function index(Request $request)
    {
        try {
            $suppliers = $this->supplierService->getAdminSuppliersList($request);
            return $this->paginatedResponse($suppliers, 'Danh sách nhà cung cấp');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi tải danh sách', 500, $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $supplier = $this->supplierService->findById($id);
            return $this->successResponse($supplier, 'Chi tiết nhà cung cấp');
        } catch (\Exception $e) {
            return $this->errorResponse('Không tìm thấy nhà cung cấp', $e->getMessage(), 404);
        }
    }

    public function store(StoreSupplierRequest $request)
    {
        try {
            $supplier = $this->supplierService->createSupplier($request->validated());
            return $this->successResponse($supplier, 'Tạo nhà cung cấp thành công', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi tạo nhà cung cấp', $e->getMessage(), 400);
        }
    }

    public function update(UpdateSupplierRequest $request, $id)
    {
        try {
            $supplier = $this->supplierService->updateSupplier($id, $request->validated());
            return $this->successResponse($supplier, 'Cập nhật thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi cập nhật', $e->getMessage(), 400);
        }
    }

    public function destroy($id)
    {
        try {
            $this->supplierService->deleteSupplier($id);
            return $this->successResponse(null, 'Đã xóa nhà cung cấp thành công');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function transactionHistory(Request $request, $id)
    {
        try {
            $data = $this->supplierService->getTransactionHistory($id, $request);

            return $this->successResponse($data, 'Lịch sử giao dịch nhà cung cấp');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi lấy lịch sử', 400, $e->getMessage());
        }
    }
}
