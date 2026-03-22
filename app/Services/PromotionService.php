<?php

namespace App\Services;

use App\Repositories\Interfaces\PromotionRepositoryInterface;
use App\Services\Interfaces\PromotionServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * PromotionService
 */
class PromotionService extends BaseService implements PromotionServiceInterface
{
    protected PromotionRepositoryInterface $promotionRepository;

    public function __construct(PromotionRepositoryInterface $promotionRepository)
    {
        parent::__construct($promotionRepository);
        $this->promotionRepository = $promotionRepository;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Lifecycle hooks (override từ BaseService)
    // ─────────────────────────────────────────────────────────────────────

    protected function beforeCreate(array $data): array
    {
        $this->validateUniqueCode($data['code']);
        return $data;
    }

    protected function beforeUpdate(int $id, array $data): array
    {
        if (isset($data['code'])) {
            $this->validateUniqueCode($data['code'], $id);
        }
        return $data;
    }

    // ─────────────────────────────────────────────────────────────────────
    // delete() – smart soft/hard delete
    // ─────────────────────────────────────────────────────────────────────

    public function delete(int $id)
    {
        DB::beginTransaction();
        try {
            $promotion = $this->promotionRepository->findByIdOrFail($id);

            $usedInOrders = DB::table('orders')
                ->where('promotion_id', $id)
                ->exists();

            if ($usedInOrders) {
                // Đã dùng trong đơn hàng → chỉ deactivate, giữ lịch sử
                $result = $this->promotionRepository->update($id, ['is_active' => false]);
            } else {
                // Chưa dùng → detach pivot rồi xóa cứng
                $promotion->products()->detach();
                $result = $this->promotionRepository->delete($id);
            }

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Business methods
    // ─────────────────────────────────────────────────────────────────────

    public function createWithProducts(array $data): \App\Models\Promotion
    {
        DB::beginTransaction();
        try {
            $productIds = $data['product_ids'] ?? [];
            unset($data['product_ids']);

            $data      = $this->beforeCreate($data);
            $promotion = $this->promotionRepository->create($data);

            if (!empty($productIds)) {
                $promotion->products()->sync($productIds);
            }

            DB::commit();
            return $promotion->load('products');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateWithProducts(int $id, array $data): \App\Models\Promotion
    {
        DB::beginTransaction();
        try {
            $productIds = $data['product_ids'] ?? null;
            unset($data['product_ids']);

            $data      = $this->beforeUpdate($id, $data);
            $promotion = $this->promotionRepository->update($id, $data);

            if ($productIds !== null) {
                $promotion->products()->sync($productIds);
            }

            DB::commit();
            return $promotion->load('products');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function toggleActive(int $id): \App\Models\Promotion
    {
        $promotion = $this->promotionRepository->findByIdOrFail($id);
        return $this->promotionRepository->update($id, [
            'is_active' => !$promotion->is_active,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────

    private function validateUniqueCode(string $code, ?int $excludeId = null): void
    {
        if ($this->promotionRepository->existsByCode($code, $excludeId)) {
            throw ValidationException::withMessages([
                'code' => ['Mã khuyến mãi đã tồn tại.'],
            ]);
        }
    }
}
