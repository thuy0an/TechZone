<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\CategoryRequest;
use App\Services\Interfaces\CategoryServiceInterface;
use Illuminate\Http\JsonResponse;

class CategoryController extends BaseApiController
{
    protected CategoryServiceInterface $categoryService;

    public function __construct(CategoryServiceInterface $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * GET /api/admin/categories
     * Danh sách danh mục (có phân trang, tìm kiếm)
     */
    public function index(): JsonResponse
    {
        try
        {
            $perPage = $this->getPerPage();
            $filters = $this->getFilters();
            $sort = $this->getSortParams();

            $categories = $this->categoryService->paginate($perPage, $filters, $sort);

            return $this->paginatedResponse($categories, 'Lấy danh sách loại sản phẩm thành công');
        }
        catch (\Exception $e)
        {
            return $this->handleException($e);
        }
    }

    /**
     * POST /api/admin/categories
     * Thêm danh mục mới
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        try
        {
            $category = $this->categoryService->create($request->validated());

            return $this->createdResponse($category, 'Thêm loại sản phẩm thành công');
        }
        catch (\Illuminate\Validation\ValidationException $e)
        {
            return $this->validationErrorResponse($e->errors());
        }
        catch (\Exception $e)
        {
            return $this->handleException($e);
        }
    }

    /**
     * GET /api/admin/categories/{id}
     * Chi tiết danh mục
     */
    public function show(int $id): JsonResponse
    {
        try
        {
            $category = $this->categoryService->findById($id);

            return $this->successResponse($category, 'Lấy thông tin loại sản phẩm thành công');
        }
        catch (\Exception $e)
        {
            return $this->handleException($e);
        }
    }

    /**
     * PUT /api/admin/categories/{id}
     * Cập nhật danh mục
     */
    public function update(CategoryRequest $request, int $id): JsonResponse
    {
        try
        {
            $category = $this->categoryService->update($id, $request->validated());

            return $this->successResponse($category, 'Cập nhật loại sản phẩm thành công');
        }
        catch (\Illuminate\Validation\ValidationException $e)
        {
            return $this->validationErrorResponse($e->errors());
        }
        catch (\Exception $e)
        {
            return $this->handleException($e);
        }
    }

    /**
     * DELETE /api/admin/categories/{id}
     * Xóa danh mục (Hard Delete)
     * Trả về 409 nếu đang có sản phẩm thuộc danh mục này
     */
    public function destroy(int $id): JsonResponse
    {
        try
        {
            $this->categoryService->delete($id);

            return $this->successResponse(null, 'Xóa loại sản phẩm thành công');
        }
        catch (\Exception $e)
        {
            if ($e->getCode() == 409) {
                return $this->errorResponse($e->getMessage(), 409);
            }
            return $this->handleException($e);
        }
    }
}
