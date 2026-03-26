<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest cho API hủy đơn hàng từ phía khách hàng.
 */
class CancelOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cancel_reason' => 'required|string|min:5|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'cancel_reason.required' => 'Vui lòng chọn lý do hủy đơn.',
            'cancel_reason.min'      => 'Lý do hủy phải có ít nhất 5 ký tự.',
            'cancel_reason.max'      => 'Lý do hủy không được vượt quá 500 ký tự.',
        ];
    }
}
