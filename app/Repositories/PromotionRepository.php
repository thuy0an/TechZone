<?php

namespace App\Repositories;

use App\Models\Promotion;
use App\Repositories\Interfaces\PromotionRepositoryInterface;
use Carbon\Carbon;

class PromotionRepository extends BaseRepository implements PromotionRepositoryInterface
{
    public function __construct(Promotion $model)
    {
        parent::__construct($model);
    }

    public function findByCode(string $code)
    {
        return $this->model->where('name', $code)
            ->where('is_active', 1) 
            ->first();
    }

    public function validatePromotion($promotion, float $cartTotal)
    {
        $now = Carbon::now();

        if (!$promotion) {
            return ['is_valid' => false, 'message' => 'Mã khuyến mãi không tồn tại hoặc đã bị khóa.'];
        }

        if ($now->lt($promotion->start_date)) {
            return ['is_valid' => false, 'message' => 'Chương trình khuyến mãi chưa bắt đầu.'];
        }
        if ($now->gt($promotion->end_date)) {
            return ['is_valid' => false, 'message' => 'Mã khuyến mãi đã hết hạn sử dụng.'];
        }

        if ($cartTotal < $promotion->min_bill_value) {
            return [
                'is_valid' => false, 
                'message' => "Đơn hàng chưa đạt giá trị tối thiểu " . number_format($promotion->min_bill_value) . "đ để áp dụng mã này."
            ];
        }

        return ['is_valid' => true, 'message' => 'Áp dụng mã thành công.'];
    }
}