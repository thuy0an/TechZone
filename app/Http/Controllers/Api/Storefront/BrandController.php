<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\Interfaces\BrandServiceInterface;
use Illuminate\Http\JsonResponse;

class BrandController extends BaseApiController
{
    protected BrandServiceInterface $brandService;

    public function __construct(BrandServiceInterface $brandService)
    {
        $this->brandService = $brandService;
    }

    /**
     * GET /api/storefront/brands
     * Danh sách thương hiệu
     */
    public function index(): JsonResponse
    {
        try {
            $brands = $this->brandService->getAll();

            return $this->successResponse($brands, 'Lấy danh sách thương hiệu thành công');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Lỗi khi lấy danh sách thương hiệu');
        }
    }
}
