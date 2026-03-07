<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,id',
            'brand_id'    => 'required|exists:brands,id',
            'code'        => 'required|string|unique:products,code',
            'name'        => 'required|string|max:255',
            'unit'        => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'profit_margin' => 'required|numeric|min:0',
            'low_stock_threshold' => 'integer|min:0',
            'status'      => 'in:visible,hidden',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Vui lòng chọn loại sản phẩm',
            'brand_id.required' => 'Vui lòng chọn thương hiệu sản phẩm',
            'code.required' => 'Vui lòng nhập mã loại sản phẩm',
            'name.required' => 'Vui lòng nhập tên sản phẩm',
        ];
    }
}
