<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyPromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'promotion_code' => 'required|string',
            'selected_product_ids' => 'nullable|array',
            'selected_product_ids.*' => 'integer|exists:products,id',
        ];
    }

    public function messages(): array
    {
        return [
            'promotion_code.required' => 'Vui lòng nhập mã khuyến mãi.',
            'promotion_code.string' => 'Mã khuyến mãi không hợp lệ.',
            'selected_product_ids.array' => 'Danh sách sản phẩm chọn không hợp lệ.',
            'selected_product_ids.*.integer' => 'Sản phẩm chọn không hợp lệ.',
            'selected_product_ids.*.exists' => 'Sản phẩm chọn không tồn tại.',
        ];
    }
}
