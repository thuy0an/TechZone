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
        ];
    }

    public function messages(): array
    {
        return [
            'promotion_code.required' => 'Vui lòng nhập mã khuyến mãi.',
            'promotion_code.string' => 'Mã khuyến mãi không hợp lệ.',
        ];
    }
}
