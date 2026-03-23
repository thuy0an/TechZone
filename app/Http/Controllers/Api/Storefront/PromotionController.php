<?php

namespace App\Http\Controllers\Api\Storefront;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\Interfaces\PromotionServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromotionController extends BaseApiController
{
    protected PromotionServiceInterface $promotionService;

    public function __construct(PromotionServiceInterface $promotionService)
    {
        $this->promotionService = $promotionService;
    }
    public function apply(Request $request)
    {
        $request->validate([
            'promotion_code' => 'required|string',
        ]);

        try {
            $userId = Auth::id();
            $result = $this->promotionService->applyPromotion($userId, $request->promotion_code);

            return $this->successResponse($result, 'Áp dụng mã khuyến mãi thành công!');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}