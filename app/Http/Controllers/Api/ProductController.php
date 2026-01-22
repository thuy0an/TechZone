<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\Interfaces\ProductServiceInterface;
use Illuminate\Http\Request;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;

class ProductController extends BaseApiController
{
    protected $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    // Lấy danh sách (có phân trang & lọc)
    public function index(Request $request)
    {
        $products = $this->productService->getAll($request->all());
        return $this->successResponse($products);
    }

    // Lấy chi tiết
    public function show($id)
    {
        $product = $this->productService->findById($id);
        if (!$product) {
            return $this->errorResponse('Không tìm thấy sản phẩm', 404);
        }
        return $this->successResponse($product);
    }

    // Tạo mới (Dành cho Admin)
    public function store(StoreProductRequest $request)
    {
        $data = $this->productService->create($request->validated());

        return $this->successResponse($data, 'Tạo sản phẩm thành công', 201);
    }

    // Cập nhật (Dành cho Admin)
    public function update(UpdateProductRequest $request, $id)
    {
        $data = $this->productService->update($id, $request->validated());
        return $this->successResponse($data, 'Cập nhật thành công');
    }

    // Xóa (Dành cho Admin)
    public function destroy($id)
    {
        $this->productService->delete($id);
        return $this->successResponse(null, 'Xóa sản phẩm thành công');
    }
}