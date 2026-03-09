<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\BrandRequest;
use App\Services\Interfaces\BrandServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends BaseApiController
{
    protected BrandServiceInterface $brandService;

    public function __construct(BrandServiceInterface $brandService)
    {
        $this->brandService = $brandService;
    }

    /**
     * GET /api/admin/brands
     * Danh sách brands (có phân trang, tìm kiếm)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $this->getPerPage();
            $filters = $this->getFilters();
            $sort    = $this->getSortParams();

            $brands = $this->brandService->paginate($perPage, $filters, $sort);

            return $this->paginatedResponse($brands, 'Lấy danh sách thương hiệu thành công');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * POST /api/admin/brands
     * Thêm mới brand
     */
    public function store(BrandRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo');
            }

            $brand = $this->brandService->create($data);

            return $this->createdResponse($brand, 'Tạo thương hiệu thành công');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * GET /api/admin/brands/{id}
     * Xem chi tiết brand
     */
    public function show(int $id): JsonResponse
    {
        try {
            $brand = $this->brandService->findById($id);

            return $this->successResponse($brand, 'Lấy thông tin thương hiệu thành công');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * PUT/POST /api/admin/brands/{id}
     * Cập nhật brand (hỗ trợ _method=PUT để upload file)
     */
    public function update(BrandRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('logo')) {
                $data['logo'] = $request->file('logo');
            }

            $brand = $this->brandService->update($id, $data);

            return $this->successResponse($brand, 'Cập nhật thương hiệu thành công');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * DELETE /api/admin/brands/{id}
     * Xóa brand
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->brandService->delete($id);

            return $this->successResponse(null, 'Xóa thương hiệu thành công');
        } catch (\Exception $e) {
            if ($e->getCode() == 409) {
                return $this->errorResponse($e->getMessage(), 409);
            }
            return $this->handleException($e);
        }
    }
}
