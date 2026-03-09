<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BrandRequest extends FormRequest
{
    /**
     * Kiểm tra nếu có quyền thực hiện request này
     */
    public function authorize(): bool
    {
        return true; // Sẽ thêm middleware auth:sanctum sau khi US-32 hoàn thành
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $brandId = $this->route('brand'); // Lấy ID từ route parameter

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:brands,name,' . $brandId
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048' // 2MB
            ]
        ];
    }

    /**
     * Thông báo lỗi tùy chỉnh.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên thương hiệu là bắt buộc',
            'name.string'   => 'Tên thương hiệu phải là chuỗi ký tự',
            'name.max'      => 'Tên thương hiệu không được vượt quá 255 ký tự',
            'name.unique'   => 'Tên thương hiệu đã tồn tại',
            'logo.image'    => 'Logo phải là file ảnh',
            'logo.mimes'    => 'Logo chỉ chấp nhận định dạng: jpg, jpeg, png',
            'logo.max'      => 'Logo không được vượt quá 2MB'
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
