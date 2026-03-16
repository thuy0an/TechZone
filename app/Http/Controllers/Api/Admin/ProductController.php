<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Services\Interfaces\ProductServiceInterface;
use Illuminate\Http\Request;

class ProductController extends BaseApiController
{
    protected ProductServiceInterface $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    // Danh sách sản phẩm
    public function index(Request $request)
    {
        try {
            $products = $this->productService->getAdminProductsList($request);
            return $this->paginatedResponse($products, 'Danh sách sản phẩm quản trị');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi tải danh sách', $e->getMessage(), 500);
        }
    }

    // Chi tiết sản phẩm
    public function show($id)
    {
        try {
            // Lấy sản phẩm kèm category và brand
            $product = $this->productService->findById($id);
            $product->load(['category', 'brand']);
            return $this->successResponse($product, 'Chi tiết sản phẩm');
        } catch (\Exception $e) {
            return $this->errorResponse('Không tìm thấy sản phẩm', $e->getMessage(), 404);
        }
    }

    // Tạo sản phẩm mới
    public function store(StoreProductRequest $request)
    {
        try {
            $product = $this->productService->createProductForAdmin($request->validated());
            return $this->createdResponse($product, 'Tạo sản phẩm thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi tạo sản phẩm', $e->getMessage(), 400);
        }
    }

    // Cập nhật sản phẩm
    public function update(UpdateProductRequest $request, $id)
    {
        try {
            $product = $this->productService->updateProductForAdmin($id, $request->validated());
            return $this->successResponse($product, 'Cập nhật sản phẩm thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi cập nhật sản phẩm', $e->getMessage(), 400);
        }
    }

    // Xóa sản phẩm
    public function destroy($id)
    {
        try {
            $result = $this->productService->deleteProductForAdmin($id);

            if ($result['type'] === 'soft_delete') {
                return $this->successResponse(
                    null,
                    'Sản phẩm đã được ẩn và xóa mềm do có lịch sử đơn hàng.'
                );
            }

            if ($result['type'] === 'force_delete') {
                return $this->successResponse(
                    null,
                    'Sản phẩm đã được xóa vĩnh viễn.'
                );
            }

            return $this->errorResponse('Không thể xác định hình thức xóa.', 400);
        } catch (\Exception $e) {
            // Đảm bảo handleException truyền đúng mã lỗi 400 hoặc 500 tùy log của bạn
            return $this->handleException($e, 'Lỗi khi thực hiện xóa sản phẩm');
        }
    }

    public function priceHistories(Request $request, $id)
    {
        try {
            $histories = $this->productService->getProductPriceHistories($id, $request);
            return $this->paginatedResponse($histories, 'Lịch sử giá sản phẩm');
        } catch (\Exception $e) {
            return $this->errorResponse('Lỗi tải lịch sử giá', 500, $e->getMessage());
        }
    }
}
