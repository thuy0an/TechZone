<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Lấy ID của sản phẩm đang được cập nhật từ URL (route param 'product')
        $productId = $this->route('product');

        return [
            'category_id'   => 'sometimes|exists:categories,id',
            'brand_id'      => 'sometimes|exists:brands,id',
            // Quan trọng: Kiểm tra duy nhất (unique) nhưng bỏ qua ID của chính sản phẩm này    
            'name'          => 'sometimes|string|max:255',
            'unit'          => 'nullable|string|max:50',
            'description'   => 'nullable|string',
            'profit_margin' => 'sometimes|numeric|min:0',
            'low_stock_threshold' => 'sometimes|integer|min:0',
            'status'        => 'sometimes|in:visible,hidden',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
            'remove_image' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Vui lòng chọn loại sản phẩm',
            'brand_id.required' => 'Vui lòng chọn thương hiệu sản phẩm',
            'name.required' => 'Vui lòng nhập tên sản phẩm',
        ];
    }
}
