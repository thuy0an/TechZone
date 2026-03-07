<?php

namespace App\Http\Requests\Admin\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:new,confirmed,shipping,delivered,completed,cancelled,failed'
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Vui lòng cung cấp trạng thái mới.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ];
    }
}
