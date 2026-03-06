<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    /**
     * Xác định xem user có quyền gọi request này không.
     * Vì chúng ta đã chặn bằng middleware 'auth:sanctum' ở route rồi nên ở đây return true.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Các quy tắc kiểm tra dữ liệu đầu vào (Validation)
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ];
    }

    /**
     * Tùy chỉnh thông báo lỗi (Tiếng Việt)
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Vui lòng chọn sản phẩm.',
            'product_id.integer' => 'Mã sản phẩm không hợp lệ.',
            'product_id.exists' => 'Sản phẩm này không tồn tại trong hệ thống.',
            'quantity.required' => 'Vui lòng nhập số lượng.',
            'quantity.integer' => 'Số lượng phải là số.',
            'quantity.min' => 'Số lượng ít nhất phải là 1.',
        ];
    }
}
