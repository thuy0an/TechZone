<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\Interfaces\ProductServiceInterface;
use App\Services\Interfaces\CategoryServiceInterface;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;


/**
 * @property ProductServiceInterface $productService
 * @property CategoryServiceInterface $categoryService
 */
class ProductController extends BaseApiController
{
    protected ProductServiceInterface $productService;
    protected CategoryServiceInterface $categoryService;

    public function __construct(
        ProductServiceInterface $productService,
        CategoryServiceInterface $categoryService
    ) {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        try {
            $products = $this->productService->getListForStorefront($request);

            return $this->paginatedResponse(
                $products,
                'Lấy danh sách sản phẩm thành công'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Lỗi khi lấy danh sách sản phẩm');
        }
    }

    public function show($id)
    {
        try {
            $product = $this->productService->getDetailForStorefront($id);

            return $this->successResponse(
                new ProductResource($product),
                'Lấy chi tiết sản phẩm thành công'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Sản phẩm không tồn tại hoặc đã bị ẩn');
        }
    }

    /**
     * Lấy danh sách tất cả danh mục
     */
    public function categories()
    {
        try {
            $categories = $this->categoryService->getAll();

            return $this->successResponse(
                $categories,
                'Lấy danh sách danh mục thành công'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Lỗi khi lấy danh sách danh mục');
        }
    }

    public function productsByCategory($categoryId, Request $request)
    {
        try {
            $products = $this->productService->getProductsByCategory($categoryId, $request);

            return $this->paginatedResponse(
                $products,
                'Lấy danh sách sản phẩm theo danh mục thành công'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Lỗi khi lấy sản phẩm theo danh mục');
        }
    }

    public function searchBasic(Request $request)
    {
        try {
            $request->validate([
                'keyword' => ['required', 'string', 'min:1'],
                'page' => ['nullable', 'integer', 'min:1'],
            ]);

            $products = $this->productService->searchBasicForStorefront($request);

            return $this->paginatedResponse(
                $products,
                'Tìm kiếm sản phẩm thành công'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Lỗi khi tìm kiếm sản phẩm');
        }
    }

    public function searchAdvanced(Request $request)
    {
        try {
            $request->validate([
                'keyword' => ['nullable', 'string'],
                'category_id' => ['nullable', 'integer'],
                'brand_id' => ['nullable', 'integer'],
                'min_price' => ['nullable', 'numeric', 'min:0'],
                'max_price' => ['nullable', 'numeric', 'min:0'],
                'page' => ['nullable', 'integer', 'min:1'],
            ]);

            $products = $this->productService->searchAdvancedForStorefront($request);

            return $this->paginatedResponse(
                $products,
                'Tìm kiếm nâng cao thành công'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Lỗi khi tìm kiếm nâng cao');
        }
    }
}
