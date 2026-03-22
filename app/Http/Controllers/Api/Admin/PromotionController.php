<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Services\Interfaces\PromotionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PromotionController extends BaseApiController
{
    protected PromotionServiceInterface $promotionService;

    public function __construct(PromotionServiceInterface $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/admin/promotions
    // ─────────────────────────────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $this->getPerPage();
            $filters = $this->getFilters();
            $sort    = $this->getSortParams();

            $promotions = $this->promotionService->paginate($perPage, $filters, $sort);

            return $this->paginatedResponse($promotions, 'Lấy danh sách khuyến mãi thành công');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // POST /api/admin/promotions
    // ─────────────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        try {
            $data      = $this->validatePromotion($request);
            $promotion = $this->promotionService->createWithProducts($data);

            return $this->createdResponse($promotion, 'Tạo khuyến mãi thành công');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // GET /api/admin/promotions/{id}
    // ─────────────────────────────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        try {
            $promotion = $this->promotionService->findById($id);
            $promotion->load('products:id,name,code');

            return $this->successResponse($promotion, 'Lấy thông tin khuyến mãi thành công');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // PUT /api/admin/promotions/{id}
    // ─────────────────────────────────────────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data      = $this->validatePromotion($request, $id);
            $promotion = $this->promotionService->updateWithProducts($id, $data);

            return $this->successResponse($promotion, 'Cập nhật khuyến mãi thành công');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // DELETE /api/admin/promotions/{id}
    // ─────────────────────────────────────────────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->promotionService->delete($id);

            return $this->successResponse(null, 'Xóa khuyến mãi thành công');
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // PATCH /api/admin/promotions/{id}/toggle-active
    // ─────────────────────────────────────────────────────────────────────
    public function toggleActive(int $id): JsonResponse
    {
        try {
            $promotion = $this->promotionService->toggleActive($id);

            return $this->successResponse(
                $promotion,
                $promotion->is_active ? 'Đã bật khuyến mãi' : 'Đã tắt khuyến mãi'
            );
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    protected function getFilters(): array
    {
        return array_merge(
            parent::getFilters(),
            request()->only(['type', 'is_active'])
        );
    }

    // ─────────────────────────────────────────────────────────────────────
    // Private: validation helper dùng chung store & update
    // ─────────────────────────────────────────────────────────────────────
    private function validatePromotion(Request $request, ?int $updateId = null): array
    {
        $codeRule = $updateId
            ? 'nullable|string|max:50'
            : 'required|string|max:50';

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'code'                => $codeRule,
            'type'                => 'required|in:discount_bill,discount_by_product',
            'discount_value'      => 'required|numeric|min:0',
            'discount_unit'       => 'required|in:percent,amount',
            'min_bill_value'      => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'start_date'          => 'nullable|date',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
            'is_active'           => 'sometimes|boolean',
            'product_ids'         => 'nullable|array',
            'product_ids.*'       => 'integer|exists:products,id',
        ]);

        if (isset($validated['is_active'])) {
            $validated['is_active'] = filter_var($validated['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        return $validated;
    }
}
