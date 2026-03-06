<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\Interfaces\ProductServiceInterface;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;


/**
 * @property ProductServiceInterface $productService
 */
class ProductController extends BaseApiController
{
    protected ProductServiceInterface $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        try {
            $products = $this->productService->getListForStorefront($request);

            return $this->successResponse(
                ProductResource::collection($products),
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
}
