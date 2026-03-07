<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CategoryRequest extends FormRequest
{
    /**
     * Kiểm tra nếu có quyền thực hiện request này
     */
    public function authorize(): bool
    {
        return true; // Sẽ thêm middleware auth:sanctum sau khi US-32 hoàn thành
    }

    public function rules(): array
    {
        $categoryId = $this->route('category'); // Lấy ID từ route parameter

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:categories,name,' . $categoryId
            ]
        ];
    }

    /**
     *  Thông báo lỗi tùy chỉnh.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên loại sản phẩm là bắt buộc',
            'name.string' => 'Tên loại sản phẩm phải là chuỗi ký tự',
            'name.max' => 'Tên loại sản phẩm không được vượt quá 255 ký tự',
            'name.unique' => 'Tên loại sản phẩm đã tồn tại'
        ];
    }

    /**
     * Trả về JSON khi validation thất bại (thay vì redirect)
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}